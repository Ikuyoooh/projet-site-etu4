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
        $stmt_qcm = $pdo->prepare("SELECT * FROM qcm WHERE id = :qcm_id AND user_id = :user_id");
        $stmt_qcm->execute([':qcm_id' => $qcm_id, ':user_id' => $user_id]);
        $qcm = $stmt_qcm->fetch(PDO::FETCH_ASSOC);

        if (!$qcm) {
            $_SESSION['error'] = "Ce QCM n'existe pas ou ne vous appartient pas.";
            header("Location: liste_qcm.php");
            exit;
        }

        $stmt_questions = $pdo->prepare("SELECT * FROM qcm_questions WHERE qcm_id = :qcm_id ORDER BY ordre ASC");
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

    if (!$titre || !$temps || !$cours || !$module) {
        echo json_encode(["success" => false, "message" => "Tous les champs du QCM sont obligatoires."]);
        exit;
    }

    if (empty($questions_data)) {
        echo json_encode(["success" => false, "message" => "Vous devez avoir au moins une question."]);
        exit;
    }

    try {
        $stmt_update = $pdo->prepare("UPDATE qcm SET titre = :titre, temps = :temps, cours = :cours, module = :module WHERE id = :qcm_id AND user_id = :user_id");
        $stmt_update->execute([
            ':titre'   => $titre,
            ':temps'   => $temps,
            ':cours'   => $cours,
            ':module'  => $module,
            ':qcm_id'  => $qcm_id,
            ':user_id' => $user_id
        ]);

        $stmt_delete = $pdo->prepare("DELETE FROM qcm_questions WHERE qcm_id = :qcm_id");
        $stmt_delete->execute([':qcm_id' => $qcm_id]);

        $stmt_insert = $pdo->prepare("INSERT INTO qcm_questions (qcm_id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, ordre) VALUES (:qcm_id, :question, :choix_1, :choix_2, :choix_3, :choix_4, :bonne_reponse, :ordre)");

        foreach ($questions_data as $index => $q) {
            $stmt_insert->execute([
                ':qcm_id'       => $qcm_id,
                ':question'     => $q['question'],
                ':choix_1'      => $q['choix_1'],
                ':choix_2'      => $q['choix_2'],
                ':choix_3'      => $q['choix_3'] ?? '',
                ':choix_4'      => $q['choix_4'] ?? '',
                ':bonne_reponse'=> intval($q['bonne_reponse']),
                ':ordre'        => $index + 1
            ]);
        }

        echo json_encode(["success" => true, "message" => "QCM modifié avec succès ! (" . count($questions_data) . " question(s))"]);
        exit;

    } catch (PDOException $e) {
        error_log("Erreur modification QCM : " . $e->getMessage());
        echo json_encode(["success" => false, "message" => "Erreur lors de la modification du QCM.", "details" => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le QCM - <?= htmlspecialchars($qcm['titre']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .question-block { border: 2px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 8px; background: #f9f9f9; }
        .question-block h3 { margin-top: 0; color: #333; }
        .choix-group { display: flex; align-items: center; margin: 10px 0; }
        .choix-group input[type="radio"] { margin-right: 10px; }
        .choix-group input[type="text"] { flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-add-question, .btn-remove-question { padding: 10px 20px; margin: 10px 5px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-add-question { background: #28a745; color: white; }
        .btn-remove-question { background: #dc3545; color: white; }
        .btn-submit { background: #007bff; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .popup { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 1000; }
        .popup.hidden { display: none; }
        .overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999; }
        .overlay.hidden { display: none; }
    </style>
</head>
<body>

<header id="header">
    <h2>Modifier le QCM</h2>
</header>

<a href="liste_qcm.php">⬅ Retour à la liste</a>

<!-- ID du QCM passé en JS — CORRIGÉ : valeur PHP correctement injectée -->
<input type="hidden" id="qcmId" value="<?= intval($qcm['id']) ?>">

<h3>Informations générales</h3>

<label>Titre du QCM *</label><br>
<!-- CORRIGÉ : value pré-remplie avec la valeur en base -->
<input type="text" id="titreQCM" value="<?= htmlspecialchars($qcm['titre']) ?>" required><br><br>

<label>Temps (minutes) *</label><br>
<input type="number" id="tempsQCM" value="<?= intval($qcm['temps']) ?>" min="1" required><br><br>

<label>Matière *</label><br>
<!-- CORRIGÉ : ternaire PHP correct pour selected -->
<select id="matiereQCM" required>
    <option value="">-- Choisir une matière --</option>
    <option value="MFER"  <?= $qcm['cours'] === 'MFER'  ? 'selected' : '' ?>>MFER</option>
    <option value="MELEC" <?= $qcm['cours'] === 'MELEC' ? 'selected' : '' ?>>MELEC</option>
    <option value="MEE"   <?= $qcm['cours'] === 'MEE'   ? 'selected' : '' ?>>MEE</option>
</select><br><br>

<label>Module *</label><br>
<!-- CORRIGÉ : boucle for fonctionnelle pour générer Module 1 à 6 -->
<select id="moduleQCM" required>
    <option value="">-- Choisir un module --</option>
    <?php for ($i = 1; $i <= 6; $i++): ?>
        <option value="Module <?= $i ?>" <?= $qcm['module'] === 'Module ' . $i ? 'selected' : '' ?>>Module <?= $i ?></option>
    <?php endfor; ?>
</select><br><br>

<h3>Questions</h3>
<div id="questionsContainer"></div>

<button type="button" class="btn-add-question" onclick="ajouterQuestion()">+ Ajouter une question</button><br><br>
<button type="button" class="btn-submit" onclick="soumettreModification()">Enregistrer les modifications</button>

<!-- Popup confirmation -->
<div class="overlay hidden" id="overlay" onclick="fermerPopup()"></div>
<div class="popup hidden" id="popup">
    <p id="popupMessage"></p>
    <button onclick="fermerPopup()">OK</button>
</div>

<script>
const questionsExistantes = <?= json_encode($questions) ?>;
let questionCount = 0;

window.addEventListener('DOMContentLoaded', function() {
    if (questionsExistantes.length > 0) {
        questionsExistantes.forEach((q) => ajouterQuestion(q));
    } else {
        ajouterQuestion();
    }
});

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
        <textarea class="question-text" rows="3" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;" required>${questionText}</textarea>
        <br><br>
        <label>Reponses possibles (cochez la bonne reponse) :</label><br>
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="1" ${bonneReponse == 1 ? 'checked' : ''}>
            <input type="text" class="choix-1" placeholder="Reponse 1" value="${choix1}" required>
        </div>
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="2" ${bonneReponse == 2 ? 'checked' : ''}>
            <input type="text" class="choix-2" placeholder="Reponse 2" value="${choix2}" required>
        </div>
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="3" ${bonneReponse == 3 ? 'checked' : ''}>
            <input type="text" class="choix-3" placeholder="Reponse 3 (optionnelle)" value="${choix3}">
        </div>
        <div class="choix-group">
            <input type="radio" name="reponse-${questionCount}" value="4" ${bonneReponse == 4 ? 'checked' : ''}>
            <input type="text" class="choix-4" placeholder="Reponse 4 (optionnelle)" value="${choix4}">
        </div>
        <br>
        <button type="button" class="btn-remove-question" onclick="supprimerQuestion('question-${questionCount}')">Supprimer cette question</button>
    `;

    container.appendChild(questionBlock);
}

function supprimerQuestion(questionId) {
    const bloc = document.getElementById(questionId);
    if (bloc) {
        bloc.remove();
    }
}

function afficherPopup(message) {
    document.getElementById('popupMessage').textContent = message;
    document.getElementById('overlay').classList.remove('hidden');
    document.getElementById('popup').classList.remove('hidden');
}

function fermerPopup() {
    document.getElementById('overlay').classList.add('hidden');
    document.getElementById('popup').classList.add('hidden');
}

async function soumettreModification() {
    const qcmId = document.getElementById('qcmId').value;
    const titre = document.getElementById('titreQCM').value.trim();
    const temps = document.getElementById('tempsQCM').value;
    const cours = document.getElementById('matiereQCM').value;
    const module = document.getElementById('moduleQCM').value;

    if (!titre || !temps || !cours || !module) {
        afficherPopup("Tous les champs du QCM sont obligatoires.");
        return;
    }

    const questionBlocks = document.querySelectorAll('.question-block');
    const questions = [];

    for (const block of questionBlocks) {
        const question = block.querySelector('.question-text')?.value.trim() || '';
        const choix_1 = block.querySelector('.choix-1')?.value.trim() || '';
        const choix_2 = block.querySelector('.choix-2')?.value.trim() || '';
        const choix_3 = block.querySelector('.choix-3')?.value.trim() || '';
        const choix_4 = block.querySelector('.choix-4')?.value.trim() || '';
        const bonneReponseInput = block.querySelector('input[type="radio"]:checked');
        const bonne_reponse = bonneReponseInput ? parseInt(bonneReponseInput.value, 10) : 0;

        if (!question || !choix_1 || !choix_2 || !bonne_reponse) {
            afficherPopup("Chaque question doit avoir un enonce, 2 choix minimum et une bonne reponse.");
            return;
        }

        questions.push({
            question,
            choix_1,
            choix_2,
            choix_3,
            choix_4,
            bonne_reponse
        });
    }

    if (questions.length === 0) {
        afficherPopup("Vous devez avoir au moins une question.");
        return;
    }

    const formData = new FormData();
    formData.append('action', 'modifier_qcm');
    formData.append('titre', titre);
    formData.append('temps', temps);
    formData.append('cours', cours);
    formData.append('module', module);
    formData.append('questions', JSON.stringify(questions));

    try {
        const response = await fetch(`modifier_qcm.php?id=${encodeURIComponent(qcmId)}`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        afficherPopup(result.message || "Reponse invalide du serveur.");

        if (result.success) {
            setTimeout(() => {
                window.location.href = 'liste_qcm.php';
            }, 900);
        }
    } catch (e) {
        afficherPopup("Erreur reseau lors de la sauvegarde.");
    }
}
</script>

</body>
</html>
