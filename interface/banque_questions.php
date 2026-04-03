<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$erreurs = [];
$message = '';

// Actions post : suppression ou import csv
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Suppression question banque
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer' && isset($_POST['id'])) {
        $id = intval($_POST['id']);

        try {
            $stmt = $pdo->prepare("DELETE FROM questions_banque WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                ':id' => $id,
                ':user_id' => $user_id
            ]);
            $message = "Question supprimée de la banque.";
        } catch (PDOException $e) {
            error_log("Erreur suppression question banque : " . $e->getMessage());
            $erreurs[] = "Erreur lors de la suppression de la question.";
        }
    }

    // Import questions depuis csv
    if (isset($_POST['action']) && $_POST['action'] === 'importer_questions' && isset($_FILES['fichier_questions'])) {
        if ($_FILES['fichier_questions']['error'] === UPLOAD_ERR_OK) {
            $fichier_tmp = $_FILES['fichier_questions']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['fichier_questions']['name'], PATHINFO_EXTENSION));

            if ($extension !== 'csv') {
                $erreurs[] = "Le fichier de questions doit être au format CSV.";
            } else {
                try {
                    $handle = fopen($fichier_tmp, 'r');
                    $nb_importees = 0;

                    while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                        if (count($data) < 6) {
                            continue;
                        }

                        $question = trim($data[0]);
                        $choix_1 = trim($data[1]);
                        $choix_2 = trim($data[2]);
                        $choix_3 = trim($data[3]);
                        $choix_4 = trim($data[4]);
                        $bonne_reponse = intval($data[5]);

                        // Ignore lignes entête (avec ou sans BOM UTF-8)
                        $question_sans_bom = ltrim($question, "\xEF\xBB\xBF");
                        if (strtolower($question_sans_bom) === 'question') {
                            continue;
                        }

                        // Ignore lignes invalides/vides
                        if ($question === '' || $choix_1 === '' || $choix_2 === '') {
                            continue;
                        }
                        if ($bonne_reponse < 1 || $bonne_reponse > 4) {
                            continue;
                        }

                        $stmt_insert = $pdo->prepare("
                            INSERT INTO questions_banque (user_id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, date_creation)
                            VALUES (:user_id, :question, :choix_1, :choix_2, :choix_3, :choix_4, :bonne_reponse, NOW())
                        ");

                        $stmt_insert->execute([
                            ':user_id' => $user_id,
                            ':question' => $question,
                            ':choix_1' => $choix_1,
                            ':choix_2' => $choix_2,
                            ':choix_3' => $choix_3,
                            ':choix_4' => $choix_4,
                            ':bonne_reponse' => $bonne_reponse
                        ]);

                        $nb_importees++;
                    }

                    fclose($handle);

                    if ($nb_importees > 0) {
                        $message = $nb_importees . " question(s) importée(s) dans la banque.";
                    } else {
                        $erreurs[] = "Aucune question valide trouvée dans le fichier.";
                    }
                } catch (PDOException $e) {
                    error_log("Erreur import questions banque : " . $e->getMessage());
                    $erreurs[] = "Erreur lors de l'import des questions.";
                }
            }
        } else {
            $erreurs[] = "Erreur lors de l'upload du fichier de questions.";
        }
    }

    // Debug : vider toutes question banque pour cet utilisateur
    if (isset($_POST['action']) && $_POST['action'] === 'vider_banque') {
        try {
            $stmt = $pdo->prepare("DELETE FROM questions_banque WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            $message = "Toute la banque de questions a été vidée pour cet utilisateur.";
        } catch (PDOException $e) {
            error_log("Erreur vider questions_banque : " . $e->getMessage());
            $erreurs[] = "Erreur lors de la suppression de toutes les questions.";
        }
    }
}

// Recup questions banque pour utilisateur
$questions_banque = [];
try {
    $stmt = $pdo->prepare("
        SELECT id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, date_creation
        FROM questions_banque
        WHERE user_id = :user_id
        ORDER BY date_creation DESC, id DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $questions_banque = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erreur récupération questions_banque : " . $e->getMessage());
    $erreurs[] = "Erreur lors de la récupération de la liste des questions.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Banque de questions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 30px;
            background: #f9f9f9;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
        .question-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .question-text {
            font-weight: bold;
            margin-bottom: 8px;
        }
        .reponses {
            list-style: none;
            padding-left: 0;
            margin: 8px 0 8px 0;
        }
        .reponses li {
            margin-bottom: 3px;
        }
        .badge-bonne {
            color: #28a745;
            font-weight: bold;
        }
        .btn-submit {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-submit:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-danger:hover {
            background: #b21f2d;
        }
        .question-meta {
            font-size: 12px;
            color: #666;
        }
        .actions-top {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .actions-top .actions-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .file-hint code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #eee;
            font-family: monospace;
        }
        .actions-top form {
            margin: 0;
        }
        input[type="file"] {
            padding: 10px;
            border: 2px dashed #007bff;
            border-radius: 6px;
            background: #fff;
            cursor: pointer;
        }
        .file-hint {
            font-size: 12px;
            color: #666;
            margin-top: 6px;
        }
        .actions-top a.btn-submit {
            text-decoration: none;
            display: inline-block;
        }
        .btn-debug {
            background: #6c757d;
        }
        .btn-debug:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>

<header id="header">
    <h2>Banque de questions</h2>
</header>

<a href="liste_qcm.php">⬅ Retour à la liste des QCM</a>

<div class="form-container">

    <div class="actions-top">
        <div class="actions-row">
            <form method="post" enctype="multipart/form-data" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <input type="hidden" name="action" value="importer_questions">
                <input type="file" name="fichier_questions" accept=".csv" required>
                <button type="submit" class="btn-submit">Importer des questions (CSV)</button>
            </form>
        </div>
        <div class="file-hint">
            Format CSV attendu : <code>question;choix_1;choix_2;choix_3;choix_4;bonne_reponse</code> (séparateur `;`, bonne réponse = 1..4).
        </div>
        <div class="actions-row">
            <a href="qcm.php" class="btn-submit" style="background:#28a745;">Créer un nouveau QCM</a>
            <form method="post" onsubmit="return confirm('DEBUG : supprimer TOUTES les questions de la banque pour cet utilisateur ?');">
                <input type="hidden" name="action" value="vider_banque">
                <button type="submit" class="btn-submit btn-debug">Debug – tout supprimer</button>
            </form>
        </div>
    </div>

    <?php if (!empty($erreurs)) : ?>
        <div class="error">
            <strong>Erreur(s) :</strong>
            <ul>
                <?php foreach ($erreurs as $erreur) : ?>
                    <li><?= htmlspecialchars($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($message) : ?>
        <div class="success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <h3>Questions enregistrées dans la banque</h3>

    <?php if (empty($questions_banque)) : ?>
        <p>Aucune question enregistrée pour le moment. Crée ou modifie un QCM pour alimenter la banque.</p>
    <?php else : ?>
        <?php foreach ($questions_banque as $q) : ?>
            <div class="question-item">
                <div class="question-header">
                    <span class="question-meta">ID #<?= intval($q['id']) ?> — Créée le <?= htmlspecialchars($q['date_creation']) ?></span>
                    <form method="post" onsubmit="return confirm('Supprimer cette question de la banque ?');">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" value="<?= intval($q['id']) ?>">
                        <button type="submit" class="btn-danger">Supprimer</button>
                    </form>
                </div>
                <div class="question-text">
                    <?= nl2br(htmlspecialchars($q['question'])) ?>
                </div>
                <ul class="reponses">
                    <li>
                        <strong>A.</strong>
                        <?= htmlspecialchars($q['choix_1']) ?>
                        <?php if ((int)$q['bonne_reponse'] === 1) : ?>
                            <span class="badge-bonne">← Bonne réponse</span>
                        <?php endif; ?>
                    </li>
                    <li>
                        <strong>B.</strong>
                        <?= htmlspecialchars($q['choix_2']) ?>
                        <?php if ((int)$q['bonne_reponse'] === 2) : ?>
                            <span class="badge-bonne">← Bonne réponse</span>
                        <?php endif; ?>
                    </li>
                    <?php if (!empty($q['choix_3'])) : ?>
                        <li>
                            <strong>C.</strong>
                            <?= htmlspecialchars($q['choix_3']) ?>
                            <?php if ((int)$q['bonne_reponse'] === 3) : ?>
                                <span class="badge-bonne">← Bonne réponse</span>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($q['choix_4'])) : ?>
                        <li>
                            <strong>D.</strong>
                            <?= htmlspecialchars($q['choix_4']) ?>
                            <?php if ((int)$q['bonne_reponse'] === 4) : ?>
                                <span class="badge-bonne">← Bonne réponse</span>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
