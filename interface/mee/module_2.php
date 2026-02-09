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
    <h1>Module 2 MEE</h1>

    <nav>
      <ul>
        <li>
          <a href="#" id="menu-toggle">Mes Cours ▼</a>
          <ul class="sous-menu" id="menu-content">
             <a href="module2\coursA\cours_A.php">Cour A</a>
            <li><a href="module2\exoA\Exo_A.php">Exo A</a></li>
            <li><a href="#">Cour B</a></li>
            <li><a href="#">Exo B</a></li>
            <li><a href="#">TP</a></li>
            <li><a href="#">Exo TP</a></li>
          </ul>
        </li>
      </ul>
    </nav>
  </main>

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
  </script>
</body>
</html>
