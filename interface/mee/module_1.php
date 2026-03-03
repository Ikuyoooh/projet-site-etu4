<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="modules_mee.css">

  <title>Site</title>

</head>
<body>
    <header>
    <img src="../../images/logo.webp" alt="Logo du site">
    </header>
    
     <p> <a href="../modules_mee.php" class="retour">◀ Retour</a></p>
  <main>
    <h1>Module 1 MEE</h1>
    <nav>
      <ul>
        <li>
          <a href="#" id="menu-toggle">Mes Cours ▼</a>
          <ul class="sous-menu" id="menu-content">
             <a href="module1\coursA\cours_A.php">Cour A</a>
            <li><a href="module1\exoA\Exo_A.php">Exo A</a></li>
            <li><a href="#">Cour B</a></li>
            <li><a href="#">Exo B</a></li>
            <li><a href="#">TP</a></li>
            <li><a href="#">Exo TP</a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </main>

  <button id="darkToggleBtn" aria-label="Basculer le mode sombre">
    <img src="../../images/moon.png" alt="Activer le mode sombre" class="dark-toggle-img" />
  </button>

  <script>
    if (localStorage.getItem('dark-mode') === 'true') {
      document.documentElement.classList.add('dark-mode');
    }

    const darkToggleBtn = document.getElementById('darkToggleBtn');
    const darkToggleImg = darkToggleBtn.querySelector('img');

    // Met à jour l’image (avec fondu)
    function updateDarkToggleImage(isDark) {
      darkToggleImg.style.opacity = '0';
      setTimeout(() => {
        darkToggleImg.src = isDark ? '../../images/sun1.png' : '../../images/moon.png';
        darkToggleImg.alt = isDark ? 'Désactiver le mode sombre' : 'Activer le mode sombre';
        darkToggleImg.style.opacity = '1';
      }, 150);
    }

    // Appliquer l’état au chargement
    window.addEventListener('DOMContentLoaded', () => {
      const isDark = localStorage.getItem('dark-mode') === 'true';
      if (isDark) {
        document.body.classList.add('dark-mode');
      }
      updateDarkToggleImage(isDark);
    });

    darkToggleBtn.addEventListener('click', () => {
      const isDark = document.body.classList.toggle('dark-mode');
      document.documentElement.classList.toggle('dark-mode');
      localStorage.setItem('dark-mode', isDark);
      updateDarkToggleImage(isDark);
    });

    // Menu déroulant
    const toggleBtn = document.getElementById("menu-toggle");
    const menuContent = document.getElementById("menu-content");

    toggleBtn.addEventListener("click", function (e) {
      e.preventDefault();
      menuContent.classList.toggle("show-menu");
    });

    document.addEventListener("click", function (e) {
      if (!toggleBtn.contains(e.target) && !menuContent.contains(e.target)) {
        menuContent.classList.remove("show-menu");
      }
    });
  </script>
</body>
</html>
