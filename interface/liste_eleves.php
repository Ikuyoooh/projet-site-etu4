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
    <h2 style="margin: 0px;"> Liste des élèves inscrits à vos cours</h2>
</header>