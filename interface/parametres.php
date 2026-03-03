<?php 
session_start();
// Activer l'affichage des erreurs dans le log
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '../logs/error.log');

// Charger le fichier XML
$xml = simplexml_load_file("../Base-de-donnees/site_db.xml");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_1 = $_POST['password_1'] ?? '';
    $password_2 = $_POST['password_2'] ?? '';

    if ($password_1 === $password_2 && !empty($password_1)) {
        $hash = password_hash($password_1, PASSWORD_DEFAULT);

        foreach ($xml->profs->prof ?? [] as $prof) {
            if ((string)$prof->id === $_SESSION['id']) {
                if (isset($prof->password)) {
                    unset($prof->password);
                }
                $prof->addChild('password', $hash);
                break;
            }
        }
        

        $xml->asXML('../Base-de-donnees/site_db.xml');
        $_SESSION['message'] = "Mot de passe mis à jour avec succès.";
    } else {
        $_SESSION['message'] = "Les mots de passe ne correspondent pas ou sont vides.";
    }
}


if (!isset($_SESSION['id'])) {
    // Pas connecté, on redirige vers la page de connexion
    header("Location: ../index.php");
    exit;
}

$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];
$formation = $_SESSION['formation'];
?>
<head>
    <title>Paramètres Professeurs</title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="javascript.js"></script>
<header id="header">
    <h2 style="margin: 0px;">Changement de mot de passe</h2>
</header>
<body>
    <div class="main">
        <a href="interface.php" class="as"><img src="../images/retour.png" id="retour"> Retour</a>
        <h2>Changement de mot de passe</h2>
        <br>
        <br>
        <form method="post">
            <div style="display: inline;">
                <div class="connexion">
                    <p>Nouveau mot de passe</p>
                    <input class="données" type="password" name="password_1" required>
                </div>
                <div class="connexion">
                    <p>Confirmer le nouveau mot de passe</p>
                    <input class="données" type="password" name="password_2" required>
                </div>
            </div>
            <?php
            if (isset($_SESSION['message'])) {
                echo '<p style="color: green;">' . htmlspecialchars($_SESSION['message']) . '</p>';
                unset($_SESSION['message']);
            }
            ?>
            <br>
            <input type="submit" value="Valider" id="connexion">
        </form>
    </div>
</body>