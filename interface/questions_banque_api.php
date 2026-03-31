<?php
session_start();
include "../conf.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$user_id = $_SESSION['id'];

try {
    $sql = "SELECT id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse 
            FROM questions_banque 
            WHERE user_id = :user_id
            ORDER BY date_creation DESC, id DESC
            LIMIT 200";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'questions' => $questions
    ]);
} catch (PDOException $e) {
    error_log("Erreur questions_banque_api : " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des questions.',
    ]);
}

