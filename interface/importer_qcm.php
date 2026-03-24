<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];

// ===== TRAITEMENT DE L'IMPORT CSV =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_csv'])) {
    
    $erreurs = [];
    $succes = false;
    
    // Vérification du fichier uploadé
    if ($_FILES['fichier_csv']['error'] === UPLOAD_ERR_OK) {
        
        $fichier_tmp = $_FILES['fichier_csv']['tmp_name'];
        $extension = strtolower(pathinfo($_FILES['fichier_csv']['name'], PATHINFO_EXTENSION));
        
        if ($extension !== 'csv') {
            $erreurs[] = "Le fichier doit être au format CSV.";
        } else {
            
            // Récupération des infos du formulaire
            $titre = trim($_POST['titre'] ?? '');
            $temps = intval($_POST['temps'] ?? 0);
            $cours = trim($_POST['cours'] ?? '');
            $module = trim($_POST['module'] ?? '');
            
            if (!$titre || !$temps || !$cours || !$module) {
                $erreurs[] = "Tous les champs du formulaire sont obligatoires.";
            } else {
                
                try {
                    // Lecture du fichier CSV
                    $handle = fopen($fichier_tmp, 'r');
                    $questions_data = [];
                    $ligne_num = 0;
                    
                    while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                        $ligne_num++;

                        // Format attendu:
                        // question;choix_1;choix_2;choix_3;choix_4;bonne_reponse
                        if (count($data) < 6) {
                            continue;
                        }

                        $question = trim($data[0]);
                        $choix_1 = trim($data[1]);
                        $choix_2 = trim($data[2]);
                        $choix_3 = trim($data[3]);
                        $choix_4 = trim($data[4]);
                        $bonne_reponse = intval($data[5]);

                        // Ignore les lignes d'entête (avec ou sans BOM UTF-8)
                        $question_sans_bom = ltrim($question, "\xEF\xBB\xBF");
                        if (strtolower($question_sans_bom) === 'question') {
                            continue;
                        }

                        // Ignore les lignes invalides/vides
                        if ($question === '' || $choix_1 === '' || $choix_2 === '') {
                            continue;
                        }
                        if ($bonne_reponse < 1 || $bonne_reponse > 4) {
                            continue;
                        }

                        $questions_data[] = [
                            'question' => $question,
                            'choix_1' => $choix_1,
                            'choix_2' => $choix_2,
                            'choix_3' => $choix_3,
                            'choix_4' => $choix_4,
                            'bonne_reponse' => $bonne_reponse
                        ];
                    }
                    fclose($handle);
                    
                    if (empty($questions_data)) {
                        $erreurs[] = "Aucune question valide trouvée dans le fichier CSV.";
                    } else {
                        
                        // 1️⃣ Créer le QCM
                        $stmt_qcm = $pdo->prepare("
                            INSERT INTO qcm (titre, temps, cours, module, date_creation, user_id)
                            VALUES (:titre, :temps, :cours, :module, NOW(), :user_id)
                        ");
                        
                        $stmt_qcm->execute([
                            ':titre' => $titre,
                            ':temps' => $temps,
                            ':cours' => $cours,
                            ':module' => $module,
                            ':user_id' => $user_id
                        ]);
                        
                        $qcm_id = $pdo->lastInsertId();
                        
                        // 2️⃣ Ajouter les questions
                        $stmt_question = $pdo->prepare("
                            INSERT INTO qcm_questions (qcm_id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, ordre)
                            VALUES (:qcm_id, :question, :choix_1, :choix_2, :choix_3, :choix_4, :bonne_reponse, :ordre)
                        ");
                        
                        foreach ($questions_data as $index => $q) {
                            $stmt_question->execute([
                                ':qcm_id' => $qcm_id,
                                ':question' => $q['question'],
                                ':choix_1' => $q['choix_1'],
                                ':choix_2' => $q['choix_2'],
                                ':choix_3' => $q['choix_3'],
                                ':choix_4' => $q['choix_4'],
                                ':bonne_reponse' => $q['bonne_reponse'],
                                ':ordre' => $index + 1
                            ]);
                        } 
                        
                        $_SESSION['success'] = "QCM importé avec succès ! (" . count($questions_data) . " question(s))";
                        header("Location: liste_qcm.php");
                        exit;
                    }
                    
                } catch (PDOException $e) {
                    error_log("Erreur import CSV : " . $e->getMessage());
                    $erreurs[] = "Erreur lors de l'import du QCM : " . $e->getMessage();
                }
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
    <title>Importer un QCM depuis CSV</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 800px;
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
        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #bee5eb;
            border-radius: 5px;
        }
        .info-box h3 {
            margin-top: 0;
        }
        .info-box code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        input[type="file"] {
            padding: 10px;
            border: 2px dashed #007bff;
            border-radius: 5px;
            width: 100%;
            margin: 10px 0;
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
        .btn-submit:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<header id="header">
    <h2>Importer un QCM depuis un fichier CSV</h2>
    <link rel="stylesheet" href="style.css">
</header>

<a href="liste_qcm.php">⬅ Retour à la liste</a>

<div class="form-container">
    
    <?php if (!empty($erreurs)) : ?>
        <div class="error">
            <strong>❌ Erreur(s) :</strong>
            <ul>
                <?php foreach ($erreurs as $erreur) : ?>
                    <li><?= htmlspecialchars($erreur) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="info-box">
        <h3>📋 Format du fichier CSV</h3>
        <p>Le fichier CSV doit contenir les colonnes suivantes, séparées par des <code>;</code> (point-virgule) :</p>
        <ol>
            <li><strong>question</strong> : Le texte de la question</li>
            <li><strong>choix_1</strong> : Première réponse possible</li>
            <li><strong>choix_2</strong> : Deuxième réponse possible</li>
            <li><strong>choix_3</strong> : Troisième réponse (optionnelle)</li>
            <li><strong>choix_4</strong> : Quatrième réponse (optionnelle)</li>
            <li><strong>bonne_reponse</strong> : Numéro de la bonne réponse (1, 2, 3 ou 4)</li>
        </ol>
        <br>
        
        <h4>Exemple de fichier CSV :</h4>
        <pre style="background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto;">question;choix_1;choix_2;choix_3;choix_4;bonne_reponse
Que signifie SQL ?;Structured Query Language;Simple Question Logic;System Quality Level;;1
Quel est le port HTTP par défaut ?;80;8080;443;3306;1
Qu'est-ce qu'une clé primaire ?;Un identifiant unique;Une clé étrangère;Un index;;1</pre>
        <br>
        
        <p><strong>⚠️ Important :</strong></p>
        <ul>
            <li>La première ligne doit contenir les en-têtes</li>
            <li>Utilisez le point-virgule <code>;</code> comme séparateur</li>
            <li>Si un choix est vide, laissez la colonne vide</li>
            <li>Encodage : <code>UTF-8</code></li>
        </ul>
    </div>
    
    <form method="post" enctype="multipart/form-data">
        <h3>📝 Informations du QCM</h3>
        
        <label>Titre du QCM *</label><br>
        <input type="text" name="titre" placeholder="Ex : QCM sur les bases de données" required>
        <br><br>
        
        <label>Temps (minutes) *</label><br>
        <input type="number" name="temps" placeholder="Ex : 30" min="1" required>
        <br><br>
        
        <label>Matière *</label><br>
        <select name="cours" required>
            <option value="">-- Choisir une matière --</option>
            <option value="MFER">MFER</option>
            <option value="MELEC">MELEC</option>
            <option value="MEE">MEE</option>
        </select>
        <br><br>
        
        <label>Module *</label><br>
        <select name="module" required>
            <option value="">-- Choisir un module --</option>
            <option value="Module 1">Module 1</option>
            <option value="Module 2">Module 2</option>
            <option value="Module 3">Module 3</option>
            <option value="Module 4">Module 4</option>
            <option value="Module 5">Module 5</option>
            <option value="Module 6">Module 6</option>
        </select>
        <br><br>
        
        <label>📂 Fichier CSV *</label><br>
        <input type="file" name="fichier_csv" accept=".csv" required>
        <br><br>
        
        <button type="submit" class="btn-submit">
            Importer le QCM</button>
    </form>
</div>

</body>
</html>
