<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Site</title>
  <link rel="stylesheet" href="exo.css">
</head>
<body>

  <header>
    <img src="../../../../images/logo.webp" alt="Logo du site MFER">
  </header>

  <p><a href="../../module_1.php" class="retour">◀ Retour</a></p>

  <main>
    <h1>Exercices A MEE</h1>
  </main>

  <div class="btn-container">
    <button class="btn" aria-label="Scrabble">
      <img src="../../../../images/scrabble.png" alt="Scrabble" class="btn-img">
      <span>Scrabble</span>
    </button>

    <button class="btn" aria-label="Mots croisés">
      <img src="../../../../images/mot_croisé2.png" alt="Mots croisés" class="btn-img">
      <span>Mots croisés</span>
    </button>

    <button class="btn" aria-label="Quiz">
      <img src="../../../../images/quiz.png" alt="Quiz" class="btn-img">
      <span>Quiz</span>
    </button>
  </div>

  <button id="darkToggleBtn" aria-label="Basculer le mode sombre">
    <img src="../../../../images/moon.png" alt="Activer le mode sombre" class="dark-toggle-img" />
  </button>

  <script>
    const darkToggleBtn = document.getElementById('darkToggleBtn');
    const darkToggleImg = darkToggleBtn.querySelector('img');

    function updateDarkToggleImage(isDark) {
      darkToggleImg.style.opacity = '0';
      setTimeout(() => {
        darkToggleImg.src = isDark ? '../../../../images/sun1.png' : '../../../../images/moon.png';
        darkToggleImg.alt = isDark ? 'Désactiver le mode sombre' : 'Activer le mode sombre';
        darkToggleImg.style.opacity = '1';
      }, 150);
    }

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
