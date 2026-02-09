<?php
session_start();
include "conf.php";

// Récupération et nettoyage du message d'erreur éventuel
$erreur = $_SESSION['erreur'] ?? '';
unset($_SESSION['erreur']);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = $_POST['identifiant'] ?? '';
    $password_input = $_POST['password'] ?? '';
    $login_reussi = false;

    // 1. Vérifie si c'est un professeur
    foreach ($pdo->query("SELECT * FROM users WHERE status = 'prof'") as $prof) {
        $nom_complet = strtolower(trim($prof['nom'])) . '.' . strtolower(trim($prof['prenom']));
        $password_db = (string)$prof['password'];
        // Accepte soit un mot de passe hashé (password_hash), soit un mot de passe en clair dans la base
        $mdp_ok = password_verify($password_input, $password_db) || $password_input === $password_db;

        if ($identifiant === $nom_complet && $mdp_ok) {
            $_SESSION['type'] = 'prof';
            $_SESSION['nom'] = (string)$prof['nom'];
            $_SESSION['prenom'] = (string)$prof['prenom'];
            $_SESSION['id'] = (string)$prof['id'];
            $_SESSION['formation'] = (string)$prof['formation'];
            $_SESSION['date'] = (string)$prof['date'];
            $login_reussi = true;

            // Redirige le prof
            header("Location: interface/interface.php");
            exit;
        }
    }

    // 2. Sinon, essaie avec les élèves
    foreach ($pdo->query("SELECT * FROM users WHERE status = 'eleve'") as $eleve) {
        $nom_complet = strtolower(trim($eleve['nom'])) . '.' . strtolower(trim($eleve['prenom']));
        $password_db = (string)$eleve['password'];
        // Accepte soit un mot de passe hashé (password_hash), soit un mot de passe en clair dans la base
        $mdp_ok = password_verify($password_input, $password_db) || $password_input === $password_db;

        if ($identifiant === $nom_complet && $mdp_ok) {
            $_SESSION['type'] = 'eleve';
            $_SESSION['nom'] = (string)$eleve['nom'];
            $_SESSION['prenom'] = (string)$eleve['prenom'];
            $_SESSION['id'] = (string)$eleve['id'];
            $_SESSION['formation'] = (string)$eleve['formation'];
            $_SESSION['date'] = (string)$eleve['date'];
            $login_reussi = true;

            // Redirige l'élève
            header("Location: interface/acceuil.php");
            exit;
        }
    }

    // 3. Sinon, essaie avec l'administrateur
    foreach ($pdo->query("SELECT * FROM users WHERE status = 'admin'") as $admin) {
        $nom_complet = strtolower(trim($admin['nom'])) . '.' . strtolower(trim($admin['prenom']));

        if ($identifiant === $nom_complet && $admin['password'] === $password_input) {
            // Redirige l'administrateur
            header("Location: backend/backend.php");
            exit;
        }
    }

    // 4. Si aucun compte ne correspond
    $_SESSION['erreur'] = "Identifiant ou mot de passe incorrect.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion</title>
  <link rel="stylesheet" href="interface/index.css" />
</head>
<body>
  <header>
    <img src="images/logo.webp" alt="Logo du site" />
  </header>

<main>
  <h1>Bienvenue</h1>
</main>
  <br>
  <p class="connect-text">Connectez-vous</p><br>

  <form method="post">
    <input type="text" name="identifiant" placeholder="nom.prénom" required><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br>
    <input type="submit" value="Se connecter">
  </form>

  <?php if (!empty($erreur)): ?>
    <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
  <?php endif; ?>




  <button id="darkToggleBtn" aria-label="Basculer le mode sombre">
    <img src="images/moon.png" alt="Activer le mode sombre" class="dark-toggle-img" />
  </button>

  <script>
    const darkToggleBtn = document.getElementById('darkToggleBtn');
    const darkToggleImg = darkToggleBtn.querySelector('img');

    // Appliquer le mode sombre dès le chargement si activé
    if (localStorage.getItem('dark-mode') === 'true') {
      document.body.classList.add('dark-mode');
      document.documentElement.classList.add('dark-mode');
    }

    function updateDarkToggleImage(isDark) {
      darkToggleImg.style.opacity = '0';
      setTimeout(() => {
        darkToggleImg.src = isDark ? 'images/sun1.png' : 'images/moon.png';
        darkToggleImg.alt = isDark ? 'Désactiver le mode sombre' : 'Activer le mode sombre';
        darkToggleImg.style.opacity = '1';}, 150);
    }

    window.addEventListener('DOMContentLoaded', () => {
      const isDark = document.body.classList.contains('dark-mode');
      updateDarkToggleImage(isDark);
    });

    darkToggleBtn.addEventListener('click', () => {
      const isDark = document.body.classList.toggle('dark-mode');
      document.documentElement.classList.toggle('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      updateDarkToggleImage(isDark);
    });


    
  </script>
</body>
</html>
