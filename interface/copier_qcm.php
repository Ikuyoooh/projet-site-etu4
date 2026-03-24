<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$qcm_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($qcm_id <= 0) {
    $_SESSION['error'] = "Aucun QCM valide a copier.";
    header("Location: liste_qcm.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // Recuperer le QCM source (appartenant a l'utilisateur)
    $stmt_qcm = $pdo->prepare("
        SELECT titre, temps, cours, module
        FROM qcm
        WHERE id = :qcm_id AND user_id = :user_id
    ");
    $stmt_qcm->execute([
        ':qcm_id' => $qcm_id,
        ':user_id' => $user_id
    ]);
    $qcm_source = $stmt_qcm->fetch(PDO::FETCH_ASSOC);

    if (!$qcm_source) {
        $pdo->rollBack();
        $_SESSION['error'] = "Ce QCM n'existe pas ou ne vous appartient pas.";
        header("Location: liste_qcm.php");
        exit;
    }

    // Inserer le nouveau QCM duplique
    $stmt_insert_qcm = $pdo->prepare("
        INSERT INTO qcm (titre, temps, cours, module, date_creation, user_id)
        VALUES (:titre, :temps, :cours, :module, NOW(), :user_id)
    ");
    $stmt_insert_qcm->execute([
        ':titre' => $qcm_source['titre'] . ' (copie)',
        ':temps' => $qcm_source['temps'],
        ':cours' => $qcm_source['cours'],
        ':module' => $qcm_source['module'],
        ':user_id' => $user_id
    ]);
    $new_qcm_id = $pdo->lastInsertId();

    // Recuperer les questions du QCM source
    $stmt_questions = $pdo->prepare("
        SELECT question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, ordre
        FROM qcm_questions
        WHERE qcm_id = :qcm_id
        ORDER BY ordre ASC
    ");
    $stmt_questions->execute([':qcm_id' => $qcm_id]);
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);

    // Inserer les questions sur le nouveau QCM
    if (!empty($questions)) {
        $stmt_insert_question = $pdo->prepare("
            INSERT INTO qcm_questions
            (qcm_id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, ordre)
            VALUES
            (:qcm_id, :question, :choix_1, :choix_2, :choix_3, :choix_4, :bonne_reponse, :ordre)
        ");

        foreach ($questions as $q) {
            $stmt_insert_question->execute([
                ':qcm_id' => $new_qcm_id,
                ':question' => $q['question'],
                ':choix_1' => $q['choix_1'],
                ':choix_2' => $q['choix_2'],
                ':choix_3' => $q['choix_3'],
                ':choix_4' => $q['choix_4'],
                ':bonne_reponse' => $q['bonne_reponse'],
                ':ordre' => $q['ordre']
            ]);
        }
    }

    $pdo->commit();
    $_SESSION['success'] = "QCM duplique avec succes.";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur duplication QCM : " . $e->getMessage());
    $_SESSION['error'] = "Erreur lors de la duplication du QCM.";
}

header("Location: liste_qcm.php");
exit;
?>
