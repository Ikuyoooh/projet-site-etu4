<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$qcm_utilisateur = [];

if (isset($pdo)) {
    try {
        
        $stmt = $pdo->prepare("
            SELECT q.*, COUNT(qq.id) as nb_questions
            FROM qcm q
            LEFT JOIN qcm_questions qq ON q.id = qq.qcm_id
            WHERE q.user_id = :user_id
            GROUP BY q.id
            ORDER BY q.date_creation DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        $qcm_utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur récupération QCM : " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes QCM</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial; }
        .qcm {
            margin: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .actions a, .actions form {
            margin-right: 10px;
            display: inline;
        }
    </style>
</head>
<body>

<header id="header">
    <h2>Mes QCM générés</h2>
</header>

<br>
<a href="importer_qcm.php">Importer un QCM</a>
<br>
<a href="qcm.php">Créer un nouveau QCM</a>
<br><br>
<h1>Mes QCM</h1>

<?php if (empty($qcm_utilisateur)) : ?>
    <p>Aucun QCM trouvé.</p>
<?php else : ?>
    <?php foreach ($qcm_utilisateur as $qcm) : ?>
        <div class="qcm">
            <strong><?= htmlspecialchars($qcm['titre']) ?></strong> 
            (<?= $qcm['nb_questions'] ?> question(s))
            <br>
            <em>
                <?= htmlspecialchars($qcm['cours']) ?>
                — <?= htmlspecialchars($qcm['module']) ?>
            </em><br>
            <span>⏱ <?= intval($qcm['temps']) ?> min</span><br>
            <small>Créé le : <?= htmlspecialchars($qcm['date_creation']) ?></small>
            
            <div class="actions">
                <a href="voir_qcm.php?id=<?= $qcm['id'] ?>">Voir</a>
                <a href="modifier_qcm.php?id=<?= $qcm['id'] ?>">Modifier</a>
                <form method="post" action="supprimer_qcm.php"
                      onsubmit="return confirm('Supprimer ce QCM ?');">
                    <input type="hidden" name="id" value="<?= $qcm['id'] ?>">
                    <button type="submit">Supprimer</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<a href="interface.php">⬅ Retour</a>

</body>
</html>

