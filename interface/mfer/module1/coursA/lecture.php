<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

// On cherche automatiquement un PDF dans le dossier "cours"
$dossier_cours = __DIR__ . '/cours1';
$pdf_a_afficher = null;

if (is_dir($dossier_cours)) {
    $liste_pdfs = glob($dossier_cours . '/*.pdf');
    if (!empty($liste_pdfs)) {
        // On prend le premier PDF trouvé
        $pdf_a_afficher = 'cours1/' . basename($liste_pdfs[0]);
    }
}

if ($pdf_a_afficher === null) {
    echo "Aucun PDF trouvé dans le dossier cours.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lecture du Cours</title>
    <!-- Feuille de style globale de l'interface -->
    <link rel="stylesheet" href="../../../style.css">
    <!-- JS global de l'interface (menu, dark mode, etc.) -->
    <script src="../../../javascript.js"></script>
    <style>
        body { font-family: Arial;}
        .pdf-container {
            width: 100%;
            height: 90vh;
        }
    </style>
</head>
<body>
    <header id="header">
    <h2 style="margin: 0px;">Cours 1 MFER</h2>
    </header>
    <h1>Contenu du cours</h1>
    <a href="cours_A.php" class="as" style="margin: 0px 1vw;"><img src="../../../../images/retour.png" id="retour"> Retour</a>

    <!-- Affichage du PDF -->
    <object class="pdf-container"
            data="<?php echo htmlspecialchars($pdf_a_afficher, ENT_QUOTES, 'UTF-8'); ?>"
            type="application/pdf">
        <p>
            Votre navigateur ne peut pas afficher le PDF.
            <a href="<?php echo htmlspecialchars($pdf_a_afficher, ENT_QUOTES, 'UTF-8'); ?>">Télécharger le cours</a>
        </p>
    </object>
</body>
</html>
