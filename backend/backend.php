<?php
session_start();

include "../conf.php";

// Activer l'affichage des erreurs dans le log
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '../logs/error.log');

// Chemin vers le fichier log
$logFile = '../logs/error.log';// Chemin vers le fichier log


$logLines = [];
if (file_exists($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // Prendre les 100 dernières lignes (ou moins si fichier plus petit)
    $logLines = array_slice($lines, -100);
}

// Supprimer un élève si formulaire de suppression soumis
if (isset($_POST['delete_id'])) {
    $id_to_delete = $_POST['delete_id'];
    error_log("Tentative de suppression de l'élève ID : $id_to_delete");

    try {
        // Supprimer l'élève dans la table unifiée `users`
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND status = 'eleve'");
        $stmt->execute([':id' => $id_to_delete]);
        error_log("Élève supprimé !");
    } catch (PDOException $e) {
        error_log("Erreur lors de la suppression : " . $e->getMessage());
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Construire et trier les élèves pour l’affichage
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE status = 'eleve' ORDER BY date DESC LIMIT 4");
    $derniers_eleves = $stmt->fetchAll(PDO::FETCH_OBJ);
    $eleves_array = !empty($derniers_eleves) ? [$derniers_eleves[0]] : [];
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des élèves : " . $e->getMessage());
    $derniers_eleves = [];
    $eleves_array = [];
}

// Compter les éléments
try {
    // Utiliser la table `users` avec le champ `status`
    $nombre_eleves = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'eleve'")->fetchColumn();
    $nombre_profs = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'prof'")->fetchColumn();
    $nombre_cours = $pdo->query("SELECT COUNT(*) FROM cours")->fetchColumn();
} catch (PDOException $e) {
    error_log("Erreur lors du comptage : " . $e->getMessage());
    $nombre_eleves = $nombre_profs = $nombre_cours = 0;
}
?>
<head>
    <title>Tableau</title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="javascript.js"></script>
<header id="header">
    <button id="toggle-menu"><img src="images/menu.webp" style="height: 20px;"></button>
    <h2 style="margin: 0px;">Backend du site</h2>
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
    <div id="tableau">
        <h3>Tableau de Bord</h3>
        <div style="display: flex;">
            <div style="background-color: aqua;" class="affichage">
                <img src="images/livre.webp">
                <p>Cours : <?= $nombre_cours ?></p>
            </div>
            <div style="background-color: blueviolet;" class="affichage">
                <img src="images/personne.webp">
                <p>Élèves : <?= $nombre_eleves ?></p>
            </div>
            <div style="background-color: brown;" class="affichage">
                <img src="images/personne.webp">
                <p>Professeurs : <?= $nombre_profs ?></p>
            </div>
        </div>
        <div><br>
            <h3>Gestion d'Élèves</h3>
        <?php if (empty($eleves_array)) : ?>
            <p>Aucun Élève trouvé.</p>
        <?php else : ?>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])): ?>
            <p style="color: green;">Élève ajouté avec succès.</p>
        <?php endif; ?>
            <table style="overflow: auto;">
                <tr>
                    <th class="ligne">Nom</th>
                    <th class="ligne">Prénom</th>
                    <th class="ligne">Formation</th>
                    <th class="ligne">Classe</th>
                    <th class="ligne">Date d'ajout</th>
                    <th class="ligne">Actions</th>
                </tr>
                <?php foreach ($derniers_eleves as $e) : ?>
                <tr>
                    <td class="ligne"><?= htmlspecialchars($e->nom ?? '') ?></td>
                    <td class="ligne"><?= htmlspecialchars($e->prenom ?? '') ?></td>
                    <td class="ligne"><?= htmlspecialchars($e->formation ?? '') ?></td>
                    <td class="ligne"><?= htmlspecialchars($e->classe ?? '') ?></td>
                    <td class="ligne"><?= htmlspecialchars($e->date ?? '') ?></td>
                    <td class="ligne">
                        <img src="images/personne.webp" class="icone">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($e->id) ?>">
                            <button type="submit" style="border: none; background: none; padding: 0; cursor: pointer;">
                                <img src="images/poubelle.webp" class="icone" alt="Supprimer" style="width: 20px; height: 20px;">
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
    <div>
        <h3>Logs</h3>
        <?php if (empty($logLines)) : ?>
            <p>Aucun log trouvé ou fichier vide.</p>
        <?php else : ?>
            <pre class="log">
            <?= htmlspecialchars(implode("\n", $logLines)) ?>
            </pre>
        <?php endif; ?>
    </div>
</div>