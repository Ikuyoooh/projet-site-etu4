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
    $_SESSION['error'] = "Aucun QCM valide a exporter.";
    header("Location: liste_qcm.php");
    exit;
}

try {
    $stmt_qcm = $pdo->prepare("
        SELECT id, titre, cours, module, temps, date_creation
        FROM qcm
        WHERE id = :qcm_id AND user_id = :user_id
    ");
    $stmt_qcm->execute([
        ':qcm_id' => $qcm_id,
        ':user_id' => $user_id
    ]);
    $qcm = $stmt_qcm->fetch(PDO::FETCH_ASSOC);

    if (!$qcm) {
        $_SESSION['error'] = "Ce QCM n'existe pas ou ne vous appartient pas.";
        header("Location: liste_qcm.php");
        exit;
    }

    $stmt_questions = $pdo->prepare("
        SELECT question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, ordre
        FROM qcm_questions
        WHERE qcm_id = :qcm_id
        ORDER BY ordre ASC
    ");
    $stmt_questions->execute([':qcm_id' => $qcm_id]);
    $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur export QCM : " . $e->getMessage());
    $_SESSION['error'] = "Erreur lors de l'export du QCM.";
    header("Location: liste_qcm.php");
    exit;
}

// Nom de fichier safe
$base_titre = preg_replace('/[^A-Za-z0-9_-]+/', '_', $qcm['titre']);
$base_titre = trim($base_titre, '_');
if ($base_titre === '') {
    $base_titre = 'qcm';
}
$filename = $base_titre . '_' . $qcm['id'] . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// BOM UTF-8 pour Excel
fwrite($output, "\xEF\xBB\xBF");

// CSV directement re-importable par importer_qcm.php
fputcsv($output, ['question', 'choix_1', 'choix_2', 'choix_3', 'choix_4', 'bonne_reponse'], ';');

foreach ($questions as $q) {
    fputcsv($output, [
        $q['question'],
        $q['choix_1'],
        $q['choix_2'],
        $q['choix_3'],
        $q['choix_4'],
        $q['bonne_reponse']
    ], ';');
}

fclose($output);
exit;
?>
