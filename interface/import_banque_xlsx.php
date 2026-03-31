<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$user_id = $_SESSION['id'];
$erreurs = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_xlsx'])) {

    if ($_FILES['fichier_xlsx']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['fichier_xlsx']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['fichier_xlsx']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'xlsx' && $ext !== 'xls') {
            $erreurs[] = "Le fichier doit être un tableur Excel (.xlsx ou .xls).";
        } else {
            try {
                $spreadsheet = IOFactory::load($tmp);
                $sheet = $spreadsheet->getActiveSheet();

                $nb_importees = 0;

                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = trim((string)$cell->getValue());
                    }

                    // On attend au moins 6 colonnes : A..F
                    if (count($rowData) < 6) {
                        continue;
                    }

                    $question       = $rowData[0];
                    $choix_1        = $rowData[1];
                    $choix_2        = $rowData[2];
                    $choix_3        = $rowData[3];
                    $choix_4        = $rowData[4];
                    $bonne_reponse  = (int)$rowData[5];

                    // Ignorer la ligne d'en-tête (si la première cellule est "question")
                    if ($rowIndex === 1) {
                        $sansBom = ltrim($question, "\xEF\xBB\xBF");
                        if (strtolower($sansBom) === 'question') {
                            continue;
                        }
                    }

                    if ($question === '' || $choix_1 === '' || $choix_2 === '') {
                        continue;
                    }
                    if ($bonne_reponse < 1 || $bonne_reponse > 4) {
                        continue;
                    }

                    $stmt = $pdo->prepare("
                        INSERT INTO questions_banque 
                            (user_id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, date_creation)
                        VALUES 
                            (:user_id, :question, :choix_1, :choix_2, :choix_3, :choix_4, :bonne_reponse, NOW())
                    ");

                    $stmt->execute([
                        ':user_id'       => $user_id,
                        ':question'      => $question,
                        ':choix_1'       => $choix_1,
                        ':choix_2'       => $choix_2,
                        ':choix_3'       => $choix_3,
                        ':choix_4'       => $choix_4,
                        ':bonne_reponse' => $bonne_reponse,
                    ]);

                    $nb_importees++;
                }

                if ($nb_importees > 0) {
                    $message = "$nb_importees question(s) importée(s) dans la banque depuis le tableur.";
                } else {
                    $erreurs[] = "Aucune question valide trouvée dans le fichier.";
                }

            } catch (Exception $e) {
                error_log("Erreur import XLSX banque : " . $e->getMessage());
                $erreurs[] = "Erreur lors de la lecture du tableur.";
            }
        }
    } else {
        $erreurs[] = "Erreur lors de l'upload du fichier.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Import banque de questions (XLSX)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header id="header">
    <h2>Importer un tableur dans la banque de questions</h2>
</header>

<a href="banque_questions.php">⬅ Retour à la banque</a>

<div class="form-container">
    <?php if (!empty($erreurs)) : ?>
        <div class="error">
            <strong>Erreur(s) :</strong>
            <ul>
                <?php foreach ($erreurs as $e) : ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($message) : ?>
        <div class="success">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <p>Choisis ton fichier Excel (.xlsx) contenant les colonnes :</p>
        <p><code>question | choix_1 | choix_2 | choix_3 | choix_4 | bonne_reponse</code></p>

        <input type="file" name="fichier_xlsx" accept=".xlsx,.xls" required>
        <br><br>
        <button type="submit" class="btn-submit">Importer dans la banque</button>
    </form>
</div>

</body>
</html>