<?php
// Activer l'affichage des erreurs dans le log
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '../logs/error.log');

// Chemin vers le fichier log
$logFile = '../logs/error.log';

// Lire les logs
$logLines = [];
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // Prendre les 100 dernières lignes (ou moins si fichier plus petit)
    $logLines = array_slice($lines, -100);
}
?>
<head>
    <title>Logs</title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="javascript.js"></script>
<header id="header">
    <button id="toggle-menu"><img src="images/menu.webp" style="height: 20px;"></button>
    <h2 style="margin: 0px;">Backend du site ####</h2>
</header>
<div class="main">
    <div id="admin" class="collapsed">
        <h3>Admin</h3>
<<<<<<< HEAD
        <a href="backend.php"><button class="button_admin">Tableau de Bord</button></a><br>
=======
        <a href="index.php"><button class="button_admin">Tableau de Bord</button></a><br>
>>>>>>> 73f165dbf481afdb2718ca6d6036256b710f1581
        <a href="eleves.php"><button class="button_admin">Élèves</button></a><br>
        <a href="profs.php"><button class="button_admin">Professeur</button></a><br>
        <a href="logs.php"><button class="button_admin">Logs</button></a><br>
    </div>
    <div style="padding: 10px;">
        <h3>Affichage des Logs (Dernières 100 lignes)</h3>
        <?php if (empty($logLines)) : ?>
            <p>Aucun log trouvé ou fichier vide.</p>
        <?php else : ?>
            <pre class="logs">
<?= htmlspecialchars(implode("\n", $logLines)) ?>
            </pre>
        <?php endif; ?>
    </div>
</div>
