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


    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      background-color: rgb(253, 253, 253);
      margin: 0px;
    }

    header {
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

/* boutons */
.btn {
    width: 250px;
    height: 200px;
    display: inline-block;
    padding: 10px 20px;
    margin: 50px;
    border: none;
    border-radius: 25px;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s ease; /* transition pour l'animation */
    transform: scale(1); /* état normal */
}

.btn:hover {
    transform: scale(1.1); /* zoom de 10% */
}

/* Couleurs des boutons */
.btn-A, .btn-B, .btn-C{
    background-color: rgb(46, 67, 90);
}

.btn-A:hover, .btn-B:hover, .btn-C:hover {
    background-color:  #3b4d75;
}

/* Centrer les boutons */
.btn-container {
    text-align: left;
    margin-top: 20px;
}

/* Centrer le texte des boutons */
 .bouton-position-A span {
    position: absolute;
    bottom: 160px;
    right: 146px;
  }

 .bouton-position-B span {
    position: absolute;
    bottom: 160px;
    right: 117px;
  }

 .bouton-position-C span {
    position: absolute;
    bottom: 160px;
    right: 182px;
  }

/* images boutons */

.img1{
    width: 116px;
    height: 116px;
    margin-left: 90px; 
    margin-top: 50px;    

}

.img2{
    width: 116px;
    height: 116px;    
    margin-left: 90px; 
    margin-top: 50px;
}

.img3{
    width: 116px;
    height: 116px;    
    margin-left: 73px; 
    margin-top: 50px;
}




  </style>
</head>
<body>

<header>
        <img src="../../../../images/logo.webp" alt="logo">
</header>
    <p> <a href="../../module_3.php" class="retour">◀ Retour</a></p>
  <main>

    <h1>Exercices A MEE</h1>

  </main>

    <div style="display: flex;">
    <button class="btn btn-A bouton-position-A"><span>Scrabble</span>
    <img src="../../../../images/scrabble.png" alt="image1" class="img1" width="100"></button>

    <button class="btn btn-B bouton-position-B"><span>Mots croisés</span> 
    <img src="../../../../images/mot_croisé2.png" alt="image2" class="img2" width="100"></button>

    <button class="btn btn-C bouton-position-C"><span>Quiz</span> 
    <img src="../../../../images/quiz.png" alt="image3" class="img3" width="100"></button>
    </div>

</body>
</html>
