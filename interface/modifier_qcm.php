<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$qcm = null;
$questions = [];

// Récupération du QCM existant
if (isset($_GET['id'])) {
    $qcm_id = intval($_GET['id']);
    
    try {
        // Récupérer les infos du QCM
        $stmt_qcm = $pdo->prepare("
            SELECT * FROM qcm 
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
        
        // Récupérer les questions
        $stmt_questions = $pdo->prepare("
            SELECT * FROM qcm_questions 
            WHERE qcm_id = :qcm_id 
            ORDER BY ordre ASC
        ");
        $stmt_questions->execute([':qcm_id' => $qcm_id]);
        $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erreur récupération QCM : " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors de la récupération du QCM.";
        header("Location: liste_qcm.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Aucun QCM spécifié.";
    header("Location: liste_qcm.php");
    exit;
}

// ===== TRAITEMENT DE LA MODIFICATION (AJAX) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier_qcm') {
    
    header('Content-Type: application/json');
    
    $titre = trim($_POST['titre'] ?? '');
    $temps = intval($_POST['temps'] ?? 0);
    $cours = trim($_POST['cours'] ?? '');
    $module = trim($_POST['module'] ?? '');
    $questions_data = json_decode($_POST['questions'] ?? '[]', true);
    
    // Validation
    if (!$titre || !$temps || !$cours || !$module) {
        echo json_encode([
            "success" => false,
            "message" => "Tous les champs du QCM sont obligatoires."
        ]);
        exit;
    }
    
    if (empty($questions_data)) {
        echo json_encode([
            "success" => false,
            "message" => "Vous devez avoir au moins une question."
        ]);
        exit;
    }
    
    try {
        // 1️⃣ Mettre à jour les informations du QCM
        $stmt_update_qcm = $pdo->prepare("
            UPDATE qcm 
            SET titre = :titre, temps = :temps, cours = :cours, module = :module
            WHERE id = :qcm_id AND user_id = :user_id
        ");
        
        $stmt_update_qcm->execute([
            ':titre' => $titre,
            ':temps' => $temps,
            ':cours' => $cours,
            ':module' => $module,
            ':qcm_id' => $qcm_id,
            ':user_id' => $user_id
        ]);
        
        // 2️⃣ Supprimer toutes les anciennes questions
        $stmt_delete = $pdo->prepare("DELETE FROM qcm_questions WHERE qcm_id = :qcm_id");
        $stmt_delete->execute([':qcm_id' => $qcm_id]);
        
        // 3️⃣ Réinsérer les nouvelles questions
        $stmt_insert = $pdo->prepare("
            INSERT INTO qcm_questions (qcm_id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, ordre)
            VALUES (:qcm_id, :question, :choix_1, :choix_2, :choix_3, :choix_4, :bonne_reponse, :ordre)
        ");
        
        foreach ($questions_data as $index => $q) {
            $stmt_insert->execute([
                ':qcm_id' => $qcm_id,
                ':question' => $q['question'],
                ':choix_1' => $q['choix_1'],
                ':choix_2' => $q['choix_2'],
                ':choix_3' => $q['choix_3'] ?? '',
                ':choix_4' => $q['choix_4'] ?? '',
                ':bonne_reponse' => intval($q['bonne_reponse']),
                ':ordre' => $index + 1
            ]);
        }
        
        echo json_encode([
            "success" => true,
            "message" => "QCM modifié avec succès ! (" . count($questions_data) . " question(s))"
        ]);
        exit;
        
    } catch (PDOException $e) {
        error_log("Erreur modification QCM : " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de la modification du QCM.",
            "details" => $e->getMessage()
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le QCM - <?= htmlspecialchars($qcm['titre']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .question-block {
            border: 2px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .question-block h3 {
            margin-top: 0;
            color: #333;
        }
        .choix-group {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .choix-group input[type="radio"] {
            margin-right: 10px;
        }
        .choix-group input[type="text"] {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn-add-question, .btn-remove-question {
            padding: 10px 20px;
            margin: 10px 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-add-question {
            background: #28a745;
            color: white;
        }
        .btn-remove-question {
            background: #dc3545;
            color: white;
        }
        .btn-submit {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        .popup.hidden {
            display: none;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .overlay.hidden {
            display: none;
        }
    </style>
</head>
<body>

<header id="header">
    <h2>Modifier le QCM</h2>
    <link rel="stylesheet" href="style.css">
</header>

<a href="liste_qcm.php" class="as"> ⬅ Retour à la liste</a>

<div class="qcm-container">
    <form id="formQCM">
        <input type="hidden" id="qcmId" value="<?= $qcm['id'] ?>">
        
        <!-- Informations générales du QCM -->
        <div class="qcm-form">
            <h3>Informations générales</h3>
            
            <label>Titre du QCM *</label>
            <input type="text" id="titreQCM" value="<?= htmlspecialchars($qcm['titre']) ?>" required>
            <br><br>
            
            <label>Temps (minutes) *</label>
            <input type="number" id="tempsQCM" value="<?= intval($qcm['temps']) ?>" min="1" required>
            <br><br>
            
            <label>Matière *</label>
            <select id="matiereQCM" required>
                <option value="">-- Choisir une matière --</option>
                <option value="MFER" <?= $qcm['cours'] == 'MFER' ? 'selected' : '' ?>>MFER</option>
                <option value="MELEC" <?= $qcm['cours'] == 'MELEC' ? 'selected' : '' ?>>MELEC</option>
                <option value="MEE" <?= $qcm['cours'] == 'MEE' ? 'selected' : '' ?>>MEE</option>
            </select>
            <br><br>
            
            <label>Module *</label>
            <select id="moduleQCM" required>
                <option value="">-- Choisir un module --</option>
                <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <option value="Module <?= $i ?>" <?= $qcm['module'] == "Module $i" ? 'selected' : '' ?>>Module <?= $i ?></option>
                <?php endfor; ?>
            </select>
            <br><br>
            
            <label>Cours *</label>
            <select id="coursQCM" required>
                <option value="">-- Choisir un cours --</option>
                <option value="COURSA" <?= $qcm['cours'] == 'COURSA' ? 'selected' : '' ?>>Cour A</option>
                <option value="COURSB" <?= $qcm['cours'] == 'COURSB' ? 'selected' : '' ?>>Cour B</option>
            </select>
            <br><br>
        </div>

        <!-- Questions -->
        <div id="questionsContainer">
            <h3>Questions</h3>
        </div>

        <button type="button" class="btn-add-question" onclick="ajouterQuestion()">
            Ajouter une question
        </button>
        <br><br>

        <button type="submit" class="btn-submit">
            Enregistrer les modifications
        </button>
    </form>
</div>

<!-- Overlay et Popup -->
<div id="overlay" class="overlay hidden"></div>
<div id="popup" class="popup hidden">
    <p id="popupMessage"></p>
    <button onclick="fermerPopup()">OK</button>
</div>

<script>
// Questions existantes du QCM
const questionsExistantes = <?= json_encode($questions) ?>;
let questionCount = 0;

// Charger les questions existantes au chargement
window.addEventListener('DOMContentLoaded', function() {
    if (questionsExistantes.length > 0) {
        questionsExistantes.forEach(q => {
            ajouterQuestion(q);
        });
    } else {
        ajouterQuestion();
    }
});

// Ajouter une question au formulaire
function ajouterQuestion(data = null) {
    questionCount++;
    
    const container = document.getElementById('questionsContainer');
    const questionBlock = document.createElement('div');
    questionBlock.className = 'question-block';
    questionBlock.id = `question-${questionCount}`;
    
    const questionText = data ? data.question : '';
    const choix1 = data ? data.choix_1 : '';
    const choix2 = data ? data.choix_2 : '';
    const choix3 = data ? data.choix_3 : '';
    const choix4 = data ? data.choix_4 : '';
    const bonneReponse = data ? data.bonne_reponse : 1;
    
    questionBlock.innerHTML = `
        <h3>Question ${questionCount}</h3>
        
        <label>Question *</label><br>
        <textarea class="question-text" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" 
                  placeholder="Ex : Que signifie l'acronyme SQL ?" required>${questionText}</textarea>
        <br><br>
        
        <label>Réponses possibles (cochez la bonne réponse) :</label><br>
        
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="1" ${bonneReponse == 1 ? 'checked' : ''}>
            <input type="text" class="choix-1" placeholder="Réponse 1" value="${choix1}" required>
        </div>
        
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="2" ${bonneReponse == 2 ? 'checked' : ''}>
            <input type="text" class="choix-2" placeholder="Réponse 2" value="${choix2}" required>
        </div>
        
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="3" ${bonneReponse == 3 ? 'checked' : ''}>
            <input type="text" class="choix-3" placeholder="Réponse 3 (optionnelle)" value="${choix3}">
        </div>
        
        <div class="choix-group">
            <input type="radio" name
