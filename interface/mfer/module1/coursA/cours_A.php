<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Site</title>
  <link rel="stylesheet" href="cour.css">
</head>
<body>
    <header>
    <img src="../../../../images/logo.webp">
    </header>
    <p> <a href="../../../modules_mfer.php" class="retour">◀ Retour</a></p>
  <main>
    <nav>
      <ul>
        <li>
          <a href="#" id="menu-toggle">
            <img src="../../../../images/liste1.png" alt="liste" class="liste courA">
            <div class="courA">Cour A MFER</div></a>
            <ul class="sous-menu" id="menu-content">
            <li><a href="lecture.php">Cour 1</a></li>
            <li><a href="medias_cours/Monteur-frigoriste.mp4">Cour 2</a></li>
            <li><a href="#">Cour 3</a></li>
            <li><a href="#">Cour 4</a></li>
            <li><a href="#">Cour 5</a></li>
            <li><a href="#">Cour 6</a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </main>

  <button id="darkToggleBtn" aria-label="Basculer le mode sombre">
    <img src="../../../../images/moon.png" alt="Activer le mode sombre" class="dark-toggle-img" />
  </button>

  <script>
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


        const darkToggleBtn = document.getElementById('darkToggleBtn');
    const darkToggleImg = darkToggleBtn.querySelector('img');

    // Met à jour l’image (avec fondu)
    function updateDarkToggleImage(isDark) {
      darkToggleImg.style.opacity = '0';
      setTimeout(() => {
        darkToggleImg.src = isDark ? '../../../../images/sun1.png' : '../../../../images/moon.png';
        darkToggleImg.alt = isDark ? 'Désactiver le mode sombre' : 'Activer le mode sombre';
        darkToggleImg.style.opacity = '1';
      }, 150);
    }

    /*RETIRER LE FLASH*/
    if (localStorage.getItem('dark-mode') === 'true') {
      document.documentElement.classList.add('dark-mode');
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


  </script>
</body>
</html>
