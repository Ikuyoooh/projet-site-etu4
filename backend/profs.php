<?php
// Activer l'affichage des erreurs dans le log
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '../logs/error.log');

require_once "../conf.php";

// Supprimer un Professeurs si formulaire de suppression soumis
if (isset($_POST['delete_id'])) {
    $id_to_delete = $_POST['delete_id'];
    error_log("Tentative de suppression du Professeur ID : $id_to_delete");

    foreach ($pdo->query("SELECT * FROM users WHERE status = 'prof'") as $prof) {
        if ((string)$prof['id'] === $id_to_delete) {
            $nom = (string)$prof['nom'];
            $prenom = (string)$prof['prenom'];
            $sql = "DELETE FROM users WHERE id = :id AND status = 'prof'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_to_delete]);
            error_log("Professeur supprimé : $nom $prenom");
            // important : sortir de la boucle dès que trouvé
            break;
        }
    }
    // Rediriger proprement
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $formation = $_POST['formation'];

    // ✅ Générer un mot de passe aléatoire de 10 caractères (stocké en clair dans la BDD)
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()'), 0, 10);

    try {
        // Ajouter dans la base de données SQL
        $nouvel_prof = $pdo->prepare("INSERT INTO users (nom, prenom, formation, date, password, status) VALUES (:nom, :prenom, :formation, :date, :password, 'prof')");
        $nouvel_prof->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':formation' => $formation,
            ':date' => date('Y-m-d H:i:s'),
            // On enregistre le mot de passe en clair pour rester compatible avec la colonne varchar(20)
            ':password' => $password
        ]);
        error_log("Professeur ajouté : " . $nom . " " . $prenom . " - Mot de passe : " . $password);
    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout d'un professeur : " . $e->getMessage());
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Construire et trier les professeurs pour l'affichage
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE status = 'prof' ORDER BY date DESC");
    $profs_array = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des professeurs : " . $e->getMessage());
    $profs_array = [];
}

?>
<head>
    <title>Profs</title>
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
    <div style="padding: 0px 10px 15px 10px;">
        <h3>Ajout ou supresion de Professeurs</h3>
        <form method="POST">
            <table>
                <tr>
                    <th>nom</th>
                    <th>prenom</th>
                </tr>
                <tr>
                    <td><input type="text" name="nom" placeholder="ex : Raboteur" required></td>
                    <td><input type="text" name="prenom" placeholder="ex : Tiago" required></td>
                </tr>
                <tr>
                    <th>formation</th>
                </tr>
                <tr>
                    <td>
                        <select name="formation" id="pet-select" required>
                          <option value="">--Choisiser une Formation--</option>
                          <option value="CIEL">CIEL</option>
                          <option value="MEE">MEE</option>
                          <option value="MFER">MFER</option>
                          <option value="MELEC">MELEC</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="submit" value="Valider"></td>
                </tr>
            </table>
        </form>
        <?php if (empty($profs_array)) : ?>
            <p>Aucun Professeurs trouvé.</p>
        <?php else : ?>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])): ?>
            <p style="color: green;">Professeur <?php $nom ?> ajouté avec succès.</p>
        <?php endif; ?>
            <table style="overflow: auto;">
                <tr>
                    <th class="ligne">Nom</th>
                    <th class="ligne">Prénom</th>
                    <th class="ligne">Formation</th>
                    <th class="ligne">Date d'ajout</th>
                    <th class="ligne">Actions</th>
                </tr>
                <?php foreach ($profs_array as $e) : ?>
                <tr>
                    <td class="ligne"><?= htmlspecialchars($e->nom ?? '') ?></td>
                    <td class="ligne"><?= htmlspecialchars($e->prenom ?? '') ?></td>
                    <td class="ligne"><?= htmlspecialchars($e->formation ?? '') ?></td>
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
</div>