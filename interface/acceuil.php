<?php
session_start();
$prenom = $_SESSION['prenom'];
$nom = $_SESSION['nom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Site</title>
  <link rel="stylesheet" href="acceuil.css">
</head>
<body>
  <header>
    <img src="../images/logo.webp" alt="Logo du site" title="Logo" />
  </header>
    <p> <a href="../index.php" class="retour">◀ Retour</a></p>

  <main>
    <div id="titre">
    <h1>Bienvenue, <?php echo htmlspecialchars($prenom); ?> <?php echo htmlspecialchars($nom); ?> !</h1>
    </div>

    <section class="btn-container" aria-labelledby="moduleTitle">

      <a href="modules_mfer.php" class="btn btn-A">
        <span>MFER</span>
        <img src="../images/mfer.png" alt="Image MFER" class="img1" title="MFER" />
      </a>

      <a href="modules_mee.php" class="btn btn-B">
        <span>MEE</span>
        <img src="../images/mee.png" alt="Image MEE" class="img2" title="MEE" />
      </a>

      <a href="modules_melec.php" class="btn btn-C">
        <span>MELEC</span>
        <img src="../images/melec.png" alt="Image MELEC" class="img3" title="MELEC" />
      </a>
    </section>
  </main>

  <button id="darkToggleBtn" aria-label="Basculer le mode sombre" role="button">
    <img src="../images/moon.png" alt="Activer le mode sombre" class="dark-toggle-img" />
  </button>

  <script>
    const darkToggleBtn = document.getElementById('darkToggleBtn');
    const darkToggleImg = darkToggleBtn.querySelector('img');

    function updateDarkToggleImage(isDark) {
      if (isDark) {
        darkToggleImg.src = '../images/sun1.png';
        darkToggleImg.alt = 'Désactiver le mode sombre';
      } else {
        darkToggleImg.src = '../images/moon.png';
        darkToggleImg.alt = 'Activer le mode sombre';
      }
    }

    window.onload = () => {
      const isDark = localStorage.getItem('dark-mode') === 'true';
      if (isDark) {
        document.body.classList.add('dark-mode');
      }
      updateDarkToggleImage(isDark);
    };

    darkToggleBtn.addEventListener('click', () => {
      const isDark = document.body.classList.toggle('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      updateDarkToggleImage(isDark);
    });

/*RETIRER LE FLASH*/
    if (localStorage.getItem('dark-mode') === 'true') {
      document.documentElement.classList.add('dark-mode');
    }
  </script>
</body>
</html>

