<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Site</title>
  <style>

    * {
      padding: 0;
      box-sizing: border-box;
    }

    img {
        height: 100px;
        margin-left: 15px; 
    }

   .retour {
      display: inline-block;
      text-decoration: none;
      color: #3b4d75;
      margin: 15px 20px;
      font-weight: bold;
    }

    header {
    background-color: rgb(30 119 153 / 58%);
    }

    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      background-color: rgb(253, 253, 253);
      margin: 0px;
    }

    div {
        background-color: rgb(30 119 153 / 58%);
    }
    main {
      padding: 20px;
      margin: 20px;
      background-color: rgb(46, 67, 90);
      border-radius: 8px;
    }

    h1 {
      color: white;
      text-align: center;
    }

    nav {
      background-color: #1f2a44;
      width: 100%;
    }

    nav ul {             
      list-style: none;
      margin: 0;
      padding: 0;
      display: block;
      width: 100%;
    }

    nav ul li {
      position: relative;
      width: 100%;
    }

    nav a#menu-toggle {
      display: block;
      padding: 15px 20px;
      color: white;
      text-decoration: none;
      cursor: pointer;
      width: 100%;
      background-color: #1f2a44;
      font-weight: bold;
      border: none;
      text-align: left;
      user-select: none;
    }

    nav a#menu-toggle:hover {
      background-color: #3b4d75;
    }

    .sous-menu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s ease;
      position: absolute;
      background-color: #1f2a44;
      top: 100%;
      left: 0;
      width: 100%;
      z-index: 1000;
      border-radius: 0 0 8px 8px;
    }

    .sous-menu.show-menu {
      max-height: 500px; /* Ajuste selon le contenu */
    }

    .sous-menu li {
      width: 100%;
    }

    .sous-menu a {
      display: block;
      padding: 10px 20px;
      color: white;
      text-decoration: none;
      background-color: #2e3d63;
      border-top: 1px solid #3b4d75;
      transition: background-color 0.3s;
    }


    
    .sous-menu a:hover {
      background-color: #3b4d75;
    }

  </style>
</head>
<body>
    <header>
      <img src="../../images/logo.webp">
    </header>
     <p> <a href="../modules_melec.php" class="retour">◀ Retour</a></p>
  <main>
    <h1>Module 1 MELEC</h1>

    <nav>
      <ul>
        <li>
          <a href="#" id="menu-toggle">Mes Cours ▼</a>
          <ul class="sous-menu" id="menu-content">
             <a href="module1/coursA/cours_A.php">Cour A</a>
            <li><a href="module1/exoA/Exo_A.php">Exo A</a></li>
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
