<<<<<<< HEAD
<?php
session_start();
include "../conf.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generer_qcm') {
    
    header('Content-Type: application/json');
    
    $titre = trim($_POST['titre'] ?? '');
    $temps = intval($_POST['temps'] ?? 0);
    $cours = trim($_POST['cours'] ?? '');
    $module = trim($_POST['module'] ?? '');
    $user_id = $_SESSION['id'];
    
    
    $questions = json_decode($_POST['questions'] ?? '[]', true);
    
    
    if (!$titre || !$temps || !$cours || !$module) {
        echo json_encode([
            "success" => false,
            "message" => "Tous les champs du QCM sont obligatoires."
        ]);
        exit;
    }
    
    if (empty($questions)) {
        echo json_encode([
            "success" => false,
            "message" => "Vous devez ajouter au moins une question."
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO qcm (titre, temps, cours, module, date_creation, user_id)
            VALUES (:titre, :temps, :cours, :module, NOW(), :user_id)
        ");
        
        $stmt->execute([
            ':titre'   => $titre,
            ':temps'   => $temps,
            ':cours'   => $cours,
            ':module'  => $module,
            ':user_id' => $user_id
        ]);
        
        $qcm_id = $pdo->lastInsertId();
        
        
        $stmt_question = $pdo->prepare("
            INSERT INTO qcm_questions (qcm_id, question, choix_1, choix_2, choix_3, choix_4, bonne_reponse, ordre)
            VALUES (:qcm_id, :question, :choix_1, :choix_2, :choix_3, :choix_4, :bonne_reponse, :ordre)
        ");
        
        foreach ($questions as $index => $q) {
            $stmt_question->execute([
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
            "message" => "QCM créé avec succès ! (" . count($questions) . " question(s))",
            "qcm_id" => $qcm_id
        ]);
        exit;
        
    } catch (PDOException $e) {
        error_log("Erreur SQL : " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de la sauvegarde du QCM.",
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
    <title>Créer un QCM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header id="header">
    <h2>Créer un QCM</h2>
</header>

<a href="liste_qcm.php" class="as"> ⬅ Retour</a>

<div class="qcm-container">
    <form id="formQCM">
        
        <div class="qcm-form">
            <h3>Informations générales</h3>
            
            <label>Titre du QCM : </label>
            <input type="text" id="titreQCM" placeholder="Ex : QCM sur les bases de données" required>
            <br><br>
            
            <label>Temps (minutes) : </label>
            <input type="number" id="tempsQCM" placeholder="Ex : 30" min="1" required>
            <br><br>
            
            <label>Matière : </label>
            <select id="matiereQCM" required>
                <option value="">-- Choisir une matière --</option>
                <option value="MFER">MFER</option>
                <option value="MELEC">MELEC</option>
                <option value="MEE">MEE</option>
            </select>
            <br><br>
            
            <label>Module : </label>
            <select id="moduleQCM" required>
                <option value="">-- Choisir un module --</option>
                <option value="Module 1">Module 1</option>
                <option value="Module 2">Module 2</option>
                <option value="Module 3">Module 3</option>
                <option value="Module 4">Module 4</option>
                <option value="Module 5">Module 5</option>
                <option value="Module 6">Module 6</option>
            </select>
            <br><br>
            
            <label>Cours : </label>
            <select id="coursQCM" required>
                <option value="">-- Choisir un cours --</option>
                <option value="COURSA">Cour A</option>
                <option value="COURSB">Cour B</option>
            </select>
            <br><br>
        </div>

        
        <div id="questionsContainer">
            <h3>Questions</h3>
            
        </div>

        <button type="button" class="btn-add-question" onclick="ajouterQuestion()">
            Ajouter une question
        </button>
        <br><br>

        <button type="submit" class="btn-submit">
            Créer le QCM
        </button>
        <br><br>
    </form>
</div>


<div id="overlay" class="overlay hidden"></div>
<div id="popup" class="popup hidden">
    <p id="popupMessage"></p>
    <button onclick="fermerPopup()">OK</button>
</div>

<script src="qcm.js"></script>
</body>
</html>
=======
<?php
session_start();
include "../conf.php";

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

// ===== GÉNÉRATION QCM (AJAX) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'generer_qcm') {

    header('Content-Type: application/json');

    $titre  = trim($_POST['titre']);
    $temps  = intval($_POST['temps']);
    $cours  = trim($_POST['cours']);
    $module = trim($_POST['module']);
    $user_id = $_SESSION['id'];

    if (!$titre || !$temps || !$cours || !$module) {
        echo json_encode([
            "success" => false,
            "message" => "Tous les champs sont obligatoires."
        ]);
        exit;
    }

    try {
        // 1️⃣ Sauvegarde SQL
        $stmt = $pdo->prepare("
            INSERT INTO qcm (titre, temps, cours, module, date_creation, user_id)
            VALUES (:titre, :temps, :cours, :module, NOW(), :user_id)
        ");
        $stmt->execute([
            ':titre' => $titre,
            ':temps' => $temps,
            ':cours' => $cours,
            ':module' => $module,
            ':user_id' => $user_id
        ]);

        $qcm_id = $pdo->lastInsertId();

        // 2️⃣ Génération du contenu QCM (simplifié ici)
        $qcm = [
            "id" => $qcm_id,
            "titre" => $titre,
            "temps" => $temps,
            "cours" => $cours,
            "module" => $module,
            "questions" => [
                [
                    "question" => "Que signifie CRM ?",
                    "choix" => [
                        "Customer Relationship Management",
                        "Client Resource Model",
                        "Central Relation Method"
                    ],
                    "bonne_reponse" => 0
                ]
            ]
        ];

        echo json_encode([
            "success" => true,
            "message" => "QCM généré et sauvegardé avec succès.",
            "qcm" => $qcm
        ]);
        exit;

    } catch (PDOException $e) {
        error_log("Erreur sauvegarde QCM : " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Erreur lors de la sauvegarde du QCM."
        ]);
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Générateur de QCM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>

<body>

<header id="header">
    <h2>Ajouter un QCM</h2>
</header>

<a href="interface.php" class="as">
    ⬅ Retour
</a>

<div class="qcm-container">

    <div class="qcm-form">
        <label>Titre du QCM</label>
        <input type="text" id="titreQCM" placeholder="Ex : QCM CIEL">

        <label>Temps (minutes)</label>
        <input type="number" id="tempsQCM" placeholder="Ex : 20">

        <label>Matière</label>
        <select id="coursQCM">
            <option value="">-- Choisir une matière --</option>
            <option value="MFER">MFER</option>
            <option value="MELEC">MELEC</option>
            <option value="MEE">MEE</option>
        </select>
        
        <label>Module</label>
        <select id="moduleQCM">
            <option value="">-- Choisir un module --</option>
            <option value="Module 1">Module 1</option>
            <option value="Module 2">Module 2</option>
            <option value="Module 3">Module 3</option>
            <option value="Module 4">Module 4</option>
            <option value="Module 5">Module 5</option>
            <option value="Module 6">Module 6</option>
        </select>

        <label>Cours</label>
        <select id="coursQCM">
            <option value="">-- Choisir un cours --</option>
            <option value="COURSA">Cour A</option>
            <option value="COURSB">Cour B</option>
        </select>

        

        <button onclick="genererQCM()">Générer le QCM</button>
    </div>

    <div id="popup" class="popup hidden">
    <p id="popupMessage"></p>
    <button onclick="fermerPopup()">OK</button>
    </div>

    <div id="resultatQCM"></div>

</div>

<script src="qcm.js"></script>
</body>
</html>
>>>>>>> 73f165dbf481afdb2718ca6d6036256b710f1581
