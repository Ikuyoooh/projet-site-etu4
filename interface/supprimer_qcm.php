<?php
session_start();
include "../conf.php";


if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    $qcm_id = intval($_POST['id']);
    $user_id = $_SESSION['id'];
    
    try {
        
        $stmt_check = $pdo->prepare("
            SELECT id FROM qcm 
            WHERE id = :qcm_id AND user_id = :user_id
        ");
        $stmt_check->execute([
            ':qcm_id' => $qcm_id,
            ':user_id' => $user_id
        ]);
        
        if ($stmt_check->rowCount() === 0) {
            $_SESSION['error'] = "Ce QCM n'existe pas ou ne vous appartient pas.";
            header("Location: liste_qcm.php");
            exit;
        }
        
        
        $stmt_questions = $pdo->prepare("
            DELETE FROM qcm_questions 
            WHERE qcm_id = :qcm_id
        ");
        $stmt_questions->execute([':qcm_id' => $qcm_id]);
        
        $nb_questions_supprimees = $stmt_questions->rowCount();
        
        
        $stmt_qcm = $pdo->prepare("
            DELETE FROM qcm 
            WHERE id = :qcm_id AND user_id = :user_id
        ");
        $stmt_qcm->execute([
            ':qcm_id' => $qcm_id,
            ':user_id' => $user_id
        ]);
        
        
        $_SESSION['success'] = "QCM supprimé avec succès ! ($nb_questions_supprimees question(s) supprimée(s))";
        
    } catch (PDOException $e) {
        error_log("Erreur suppression QCM : " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors de la suppression du QCM.";
    }
    
} else {
    $_SESSION['error'] = "Requête invalide.";
}


header("Location: liste_qcm.php");
exit;
?>
