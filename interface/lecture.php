<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['fichier'])) {
    echo "Aucun fichier spécifié.";
    exit;
}

$utilisateur_id = $_SESSION['id'];
$fichier = basename($_GET['fichier']); // sécurisation
$nom = pathinfo($fichier, PATHINFO_FILENAME);
$chemin = "cours/" . $utilisateur_id . "/". $fichier;

if (!file_exists($chemin)) {
    echo "Fichier introuvable.";
    exit;
}

$contenu = file_get_contents($chemin);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lecture de <?= htmlspecialchars($nom) ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="javascript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body { font-family: Arial;}
        #markdownContent { border: 1px solid #ccc; padding: 20px; margin: 20px; }
    </style>
</head>
<body>
    <header id="header">
    <h2 style="margin: 0px;">Lecture de <?= htmlspecialchars($nom) ?></h2>
    </header>
    <h1>Contenu de <?= htmlspecialchars($nom) ?></h1>
    <a href="liste_cours.php">⬅ Retour</a>
    <div id="markdownContent"></div>
    <script>
        const markdown = <?php echo json_encode($contenu); ?>;
        document.getElementById("markdownContent").innerHTML = marked.parse(markdown);
    </script>
</body>
</html>
