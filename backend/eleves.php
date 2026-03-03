<?php
// Activer l'affichage des erreurs dans le log
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '../logs/error.log');

require_once "../conf.php";

if (isset($_POST['delete_id'])) {
    $id_to_delete = $_POST['delete_id'];
    error_log("Tentative de suppression de l'élève ID : $id_to_delete");

    foreach ($pdo->query("SELECT * FROM users WHERE status = 'eleve'") as $eleve) {
        if ((string)$eleve['id'] === $id_to_delete) {
            $nom = (string)$eleve['nom'];
            $prenom = (string)$eleve['prenom'];
            $sql = "DELETE FROM users WHERE id = :id AND status = 'eleve'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_to_delete]);
            error_log("Élève supprimé : $nom $prenom");
            // important : sortir de la boucle dès que trouvé
            break;
        }
    }

    // Rediriger proprement
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nom'])) {
    $nom       = $_POST['nom'];
    $prenom    = $_POST['prenom'];
    $formation = $_POST['formation'];
    $classe    = $_POST['classe'];

    // Générer un mot de passe aléatoire (stocké en clair dans la BDD)
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^*'), 0, 10);

    try {
        // Ajouter dans la base de données SQL
        $nouvel_eleve = $pdo->prepare("INSERT INTO users (nom, prenom, formation, classe, date, password, status) VALUES (:nom, :prenom, :formation, :classe, :date, :password, 'eleve')");
        $nouvel_eleve->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':formation' => $formation,
            ':classe' => $classe,
            ':date' => date('Y-m-d H:i:s'),
            // On enregistre le mot de passe en clair pour rester compatible avec la colonne varchar(20)
            ':password' => $password
        ]);
        error_log("Élève ajouté : " . $nom . " " . $prenom . " - Mot de passe : " . $password);
    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout d'un élève : " . $e->getMessage());
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Construire et trier les élèves pour l’affichage
try {
    $stmt = $pdo->query("SELECT * FROM users WHERE status = 'eleve' ORDER BY date DESC");
    $eleves_array = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des élèves : " . $e->getMessage());
    $eleves_array = [];
}

?>
<head>
    <title>Élève</title>
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
        <h3>Ajout ou supresion d'Élève</h3>
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
                    <th>classe</th>
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
                    <td><input type="text" name="classe" placeholder="ex : CIEL1" required></td>
                </tr>
                <tr>
                    <td><input type="submit" value="Valider"></td>
                </tr>
            </table>
        </form>
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
                <?php foreach ($eleves_array as $e) : ?>
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
</div>