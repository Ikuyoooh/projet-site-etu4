<?php
session_start();

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
    <title>Interface Professeurs</title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="javascript.js"></script>
<header id="header">
    <img src="../images/logo.webp" alt="Logo du site" title="Logo" />
    <h2 style="margin: 15px;"> Interface Professeurs</h2>
</header>
<body>
    <div class="main">
        <h2>Bienvenue <?= htmlspecialchars($prenom) ?> <?= htmlspecialchars($nom) ?> !</h2>
    </div>
    <a href="../index.php" class="as" style="margin: 0px 1vw;"><img src="../images/retour.png" id="retour"> Retour</a>
    <div>
        <br>
        <div id="main">
            <div class="menu_block">
                <button type="button" class="bouton_menu">Cours</button>
                <div class="cours caché">
                    <a href="cours.php"><div class="a">Ajouter un Cours</div></a>
                    <a href="qcm.php"><div class="a">Ajouter un QCM</div></a>
                    <a href="liste_cours.php"><div class="a">Voir les Cours</div></a>
                    <a href="liste_qcm.php"><div class="a">Voir les QCM</div></a>
                </div>
            </div>

            <div class="menu_block">
                <button type="button" class="bouton_menu">Élève</button>
                <div class="cours caché">
                    <a href="liste_eleves.php"><div class="a">Voir les élèves inscrits à vos cours</div></a>
                </div>
            </div>

            <div class="menu_block">
                <button type="button" class="bouton_menu">Paramètres</button>
                <div class="cours caché">
                    <a href="parametres.php"><div class="a">Changer votre mot de passe</div></a>
                </div>
            </div>
        </div>
    </div>
</body>