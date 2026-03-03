<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Site</title>
  <link rel="stylesheet" href="modules.css">

</head>

<body>
  <header>
    <img src="../images/logo.webp" alt="Logo du site">
  </header>

  <p><a href="acceuil.php" class="retour">◀ Retour</a></p>

  <main>
    <h1>Modules MFER</h1>

    <nav>
      <ul>
        <li>
          <a href="#" id="menu-toggle">Mes Modules ▼</a>
          <ul class="sous-menu" id="menu-content">
            <li><a href="mfer/module1/coursA/cours_A.php">Module 1</a></li>
            <li><a href="mfer/module_2.php">Module 2</a></li>
            <li><a href="mfer/module_3.php">Module 3</a></li>
            <li><a href="#">Module 4</a></li>
            <li><a href="#">Module 5</a></li>
            <li><a href="#">Module 6</a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </main>

  <button id="darkToggleBtn" aria-label="Basculer le mode sombre">
    <img src="../images/moon.png" alt="Activer le mode sombre" class="dark-toggle-img" />
  </button>

  <script>
    const darkToggleBtn = document.getElementById('darkToggleBtn');
    const darkToggleImg = darkToggleBtn.querySelector('img');

    // Appliquer le thème sombre immédiatement si déjà activé
    if (localStorage.getItem('dark-mode') === 'true') {
      document.body.classList.add('dark-mode');
    }

    // Met à jour l'image (avec transition)
    function updateDarkToggleImage(isDark) {
      darkToggleImg.style.opacity = '0';
      setTimeout(() => {
        darkToggleImg.src = isDark ? '../images/sun1.png' : '../images/moon.png';
        darkToggleImg.alt = isDark ? 'Désactiver le mode sombre' : 'Activer le mode sombre';
        darkToggleImg.style.opacity = '1';
      }, 150);
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

    /*RETIRER LE FLASH*/
    if (localStorage.getItem('dark-mode') === 'true') {
      document.documentElement.classList.add('dark-mode');
    }
    

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
