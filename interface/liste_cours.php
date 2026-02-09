<?php
session_start();
include "../conf.php";
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}


$user_id = $_SESSION['id'];
$dossier = "cours/" . $user_id . "/";

$cours_utilisateur = [];

// 1) Sélectionner les cours en base pour cet utilisateur (si la table existe / est remplie)
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM cours WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        foreach ($stmt->fetchAll() as $cours) {
            $cours_utilisateur[] = $cours;
        }
    } catch (PDOException $e) {
        error_log("Erreur récupération cours SQL : " . $e->getMessage());
    }
}

// 2) Si aucun cours en base, on tente un fallback en lisant directement le dossier cours/{user_id}
if (empty($cours_utilisateur) && is_dir($dossier)) {
    foreach (glob($dossier . '*.md') as $chemin_fichier) {
        $cours_utilisateur[] = [
            'titre' => basename($chemin_fichier),
            'fichier' => basename($chemin_fichier),
            'date_modification' => date('d/m/Y H:i:s', filemtime($chemin_fichier)),
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des cours</title>
    <link rel="stylesheet" href="style.css">
    <script src="javascript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body { font-family: Arial; }
        .cours { margin-bottom: 15px; padding: 10px; border: 1px solid #ccc; margin: 20px; }
        .actions a, .actions form { margin-right: 10px; display: inline; }
    </style>
</head>
<body>

<header id="header">
    <h2 style="margin: 0px;">Mes cours sauvegardés</h2>
</header>

<h1>Mes cours sauvegardés</h1>

<?php
if (empty($cours_utilisateur)) {
    echo "<p>Aucun cours trouvé.</p>";
} else {
    foreach ($cours_utilisateur as $cours) {
        $titre = $cours['titre'] ?? '';
        $date = $cours['date_modification'] ?? '';
        $fichier = $cours['fichier'] ?? '';
        $chemin = $dossier . $fichier;
        $nomSansExtension = pathinfo($fichier, PATHINFO_FILENAME);

        $chemin_complet = __DIR__ . '/' . $chemin;

        if (!file_exists($chemin_complet)) continue;
        ?>
        <div class="cours">
            <strong><?= htmlspecialchars($nomSansExtension) ?></strong><br>
            <em>Modifié le : <?= htmlspecialchars($date) ?></em><br>
            <div class="actions">
                <a href="lecture.php?user=<?= urlencode($user_id) ?>&fichier=<?= urlencode($fichier) ?>">Voir</a>
                <a href="modifier.php?user=<?= urlencode($user_id) ?>&fichier=<?= urlencode($fichier) ?>">Modifier</a>
                <form method="post" action="supprimer.php" onsubmit="return confirm('Supprimer ce cours ?');" style="display:inline;">
                    <input type="hidden" name="user" value="<?= htmlspecialchars($user_id) ?>">
                    <input type="hidden" name="fichier" value="<?= htmlspecialchars($fichier) ?>">
                    <button type="submit">Supprimer</button>
                </form>
            </div>
        </div>
        <?php
    }
}
?>

<a href="interface.php">⬅ Retour</a>

</body>
</html>
