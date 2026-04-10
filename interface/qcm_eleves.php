<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$cours = trim($_GET['cours'] ?? '');
$module = trim($_GET['module'] ?? '');

$qcms = [];

if ($cours !== '' && $module !== '') {
    $stmt = $pdo->prepare("
        SELECT id, titre, temps, cours, module, date_creation
        FROM qcm
        WHERE cours = :cours AND module = :module
        ORDER BY date_creation DESC
    ");
    $stmt->execute([
        ':cours' => $cours,
        ':module' => $module
    ]);
    $qcms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QCM élèves</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>QCM - <?= htmlspecialchars($cours) ?> - <?= htmlspecialchars($module) ?></h1>
    <a href="javascript:history.back()">⬅ Retour</a>

    <?php if (empty($qcms)): ?>
        <p>Aucun QCM disponible pour cette matière.</p>
    <?php else: ?>
        <?php foreach ($qcms as $qcm): ?>
            <div class="qcm">
                <h3><?= htmlspecialchars($qcm['titre']) ?></h3>
                <p>Temps : <?= (int)$qcm['temps'] ?> min</p>
                <p>Créé le : <?= htmlspecialchars($qcm['date_creation']) ?></p>
                <a href="voir_qcm_eleve.php?id=<?= (int)$qcm['id'] ?>">Voir le QCM</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html> 