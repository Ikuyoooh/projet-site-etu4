<?php
session_start();
include "../conf.php";

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['id'];
$qcm = null;
$questions = [];

// recup id qcm
if (isset($_GET['id'])) {
    $qcm_id = intval($_GET['id']);
    
    try {
        //Recup infos qcm
        $stmt_qcm = $pdo->prepare("
            SELECT * FROM qcm 
            WHERE id = :qcm_id AND user_id = :user_id
        ");
        $stmt_qcm->execute([
            ':qcm_id' => $qcm_id,
            ':user_id' => $user_id
        ]);
        $qcm = $stmt_qcm->fetch(PDO::FETCH_ASSOC);
        
        if (!$qcm) {
            $_SESSION['error'] = "Ce QCM n'existe pas ou ne vous appartient pas.";
            header("Location: liste_qcm.php");
            exit;
        }
        
        // Recup questions qcm
        $stmt_questions = $pdo->prepare("
            SELECT * FROM qcm_questions 
            WHERE qcm_id = :qcm_id 
            ORDER BY ordre ASC
        ");
        $stmt_questions->execute([':qcm_id' => $qcm_id]);
        $questions = $stmt_questions->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Erreur récupération QCM : " . $e->getMessage());
        $_SESSION['error'] = "Erreur lors de la récupération du QCM.";
        header("Location: liste_qcm.php");
        exit;
    }
    
} else {
    $_SESSION['error'] = "Aucun QCM spécifié.";
    header("Location: liste_qcm.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir QCM - <?= htmlspecialchars($qcm['titre']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .qcm-header {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #007bff;
        }
        .qcm-header h1 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .qcm-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin: 10px 0;
        }
        .qcm-info span {
            background: white;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .question-block {
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .question-block h3 {
            color: #007bff;
            margin-top: 0;
        }
        .question-text {
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            color: #333;
        }
        .choix-item {
            padding: 12px;
            margin: 8px 0;
            border-radius: 5px;
            border: 2px solid #e0e0e0;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .choix-item.correct {
            background: #d4edda;
            border-color: #28a745;
            font-weight: bold;
        }
        .choix-item .badge {
            background: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .choix-item.correct .badge {
            background: #28a745;
        }
        .actions-bar {
            margin: 30px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-warning { background:rgb(255, 188, 2); color: #333; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .no-questions {
            padding: 40px;
            text-align: center;
            background: #fff3cd;
            border: 2px dashed #ffc107;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<header id="header">
    <h2>Visualisation du QCM</h2>
</header>

<a href="liste_qcm.php">⬅ Retour à la liste</a>

<!-- Informations du QCM -->
<div class="qcm-header">
    <h1><?= htmlspecialchars($qcm['titre']) ?></h1>
    <div class="qcm-info">
        <span><?= htmlspecialchars($qcm['cours']) ?></span>
        <span><?= htmlspecialchars($qcm['module']) ?></span>
        <span><?= intval($qcm['temps']) ?> minutes</span>
        <span><?= count($questions) ?> question(s)</span>
    </div>
    <small style="color: #666;">Créé le : <?= htmlspecialchars($qcm['date_creation']) ?></small>
</div>


<div class="actions-bar">
    <a href="modifier_qcm.php?id=<?= $qcm['id'] ?>" class="btn btn-warning">
        Modifier ce QCM
    </a>
    <form method="post" action="supprimer_qcm.php" style="display: inline;"
          onsubmit="return confirm('Supprimer ce QCM et ses <?= count($questions) ?> question(s) ?');">
        <input type="hidden" name="id" value="<?= $qcm['id'] ?>">
        <button type="submit" class="btn btn-danger">Supprimer</button>
    </form>
    <button onclick="window.print()" class="btn btn-secondary">Imprimer</button>
    <a href="exporter_qcm.php?id=<?= $qcm['id'] ?>" class="btn btn-primary">Exporter le QCM</a>
</div>


<?php if (empty($questions)) : ?>
    <div class="no-questions">
        <h3>Aucune question dans ce QCM</h3>
        <p>Ce QCM ne contient pas de question pour le moment.</p>
        <a href="modifier_qcm.php?id=<?= $qcm['id'] ?>" class="btn btn-primary">
            Ajouter des questions
        </a>
    </div>
<?php else : ?>
    <?php foreach ($questions as $index => $question) : ?>
        <div class="question-block">
            <h3>Question <?= $index + 1 ?></h3>
            <div class="question-text">
                <?= nl2br(htmlspecialchars($question['question'])) ?>
            </div>
            
            <div class="choix-list">
                <?php
                $choix = [
                    1 => $question['choix_1'],
                    2 => $question['choix_2'],
                    3 => $question['choix_3'],
                    4 => $question['choix_4']
                ];
                
                foreach ($choix as $num => $texte) {
                    if (!empty($texte)) {
                        $isCorrect = ($num == $question['bonne_reponse']);
                        $class = $isCorrect ? 'choix-item correct' : 'choix-item';
                        ?>
                        <div class="<?= $class ?>">
                            <span class="badge"><?= chr(64 + $num) ?></span>
                            <span><?= htmlspecialchars($texte) ?></span>
                            <?php if ($isCorrect) : ?>
                                <span style="margin-left: auto; color: #28a745;">✓ Bonne réponse</span>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div style="margin-top: 30px;">
    <a href="liste_qcm.php" class="btn btn-secondary">⬅ Retour à la liste</a>
</div>

</body>
</html>
