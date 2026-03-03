<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['fichier'])) {
    die("Fichier non spécifié.");
}

$utilisateur_id = $_SESSION['id'];
$fichier = basename($_GET['fichier']);
$nomCoursSansExtension = pathinfo($fichier, PATHINFO_FILENAME);
$chemin = "cours/" . $utilisateur_id . "/". $fichier;

if (!file_exists($chemin)) {
    die("Fichier introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Cas 1 : suppression
    if (isset($_POST['supprimer'])) {
        unlink($chemin);
        header("Location: liste_cours.php");
        exit;
    }

    // Cas 2 : enregistrement markdown depuis formulaire HTML
    if (isset($_POST['contenu'])) {
        file_put_contents($chemin, $_POST['contenu']);
        header("Location: lecture.php?fichier=" . urlencode($nom));
        exit;
    }

    // Cas 3 : sauvegarde depuis JS (AJAX)
    if (isset($_POST['markdown'])) {
        file_put_contents($chemin, $_POST['markdown']);
        echo json_encode(["success" => true]);
        exit;
    }

    // Cas 4 : upload PDF
    if (isset($_FILES['pdf'])) {
        $uploadDir = "docs/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = basename($_FILES['pdf']['name']);
        $targetPath = $uploadDir . time() . "_" . preg_replace('/[^a-zA-Z0-9\.\-_]/', '_', $fileName);

        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
            echo json_encode(["success" => true, "url" => $targetPath]);
        } else {
            echo json_encode(["success" => false, "message" => "Échec de l'upload du fichier."]);
        }
        exit;
    }
}


$contenu = file_get_contents($chemin);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le cours</title>
    <link rel="stylesheet" href="style.css">
    <script src="javascript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body>
    <header id="header">
        <h2 style="margin: 0px;">Modifier un Cours</h2>
    </header>
    <h1>Modifier le cours</h1>
    <a href="liste_cours.php" class="as" style="margin: 0px 1vw;"><img src="../images/retour.png" id="retour"> Retour</a>

    <label for="nomCours" style="margin: 0px 1vw;">Titre du cours (sans extension) :</label>

    <input type="text" id="nomCours" placeholder="ex: cours_01" style="width: 15vw;" value="<?= htmlspecialchars($nomCoursSansExtension) ?>">
    <div class="main" style="display: flex;">
        <div style="max-width: 100%;">
            <form method="post">
                <div class="toolbar">
                    <button onclick="addMarkdown('**', '**')" type="button"><img src="../images/gras.png" title="Gras"></button>
                    <button onclick="addMarkdown('*', '*')" type="button"><img src="../images/italique.png" title="Italique"></button>
                    <button onclick="addMarkdown('![Texte Alt](URL)', '')" type="button"><img src="../images/image.webp" title="Image"></button>
                    <button onclick="addMarkdown('[Texte du lien](URL)', '')" type="button"><img src="../images/lien.webp" title="Lien"></button>
                    <button onclick="addMarkdown('```\\n', '\\n```')" type="button"><img src="../images/code.webp" title="Code"></button>
                    <button onclick="addMarkdown('# ', '')" type="button"><img src="../images/titre.png" title="Titre"></button>
                    <label style="cursor: pointer;">
                        <img src="../images/pdf.webp" title="Téléverser un PDF" style="height: 25px;">
                        <input type="file" id="uploadPDFInput" accept="application/pdf" style="display: none;">
                    </label>
                    <button type="button" onclick="sauvegarderMarkdown()">
                        <img src="../images/sauvegarder.png" title="Sauvegarder">
                    </button>
                </div>

                <textarea name="contenu" rows="20" cols="100" id="editor"><?= htmlspecialchars($contenu) ?></textarea>
            </form>
        </div>
        <div id="preview"></div>
    </div>

    <script>
    function renderMarkdown() {
        const input = document.getElementById('editor').value;
        document.getElementById('preview').innerHTML = marked.parse(input);
    }
    document.addEventListener('DOMContentLoaded', () => {
        renderMarkdown();
        document.getElementById('editor').addEventListener('input', renderMarkdown);
    });
    </script>
</body>
</html>
