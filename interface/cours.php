<?php
session_start();
include "../conf.php";

// Activer l'affichage des erreurs dans le log
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '../logs/error.log');

// Préparation du fichier XML des cours
$xml_path = '../Base-de-donnees/site_db.xml';
if (file_exists($xml_path)) {
    $xml = simplexml_load_file($xml_path);
} else {
    // S'assurer que le dossier existe
    if (!is_dir('../Base-de-donnees')) {
        mkdir('../Base-de-donnees', 0775, true);
    }
    // Créer une structure XML de base
    $xml = new SimpleXMLElement('<site></site>');
}

if (!isset($_SESSION['id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "Non autorisé."]);
    } else {
        header("Location: ../index.php");
    }
    exit;
}

// === Gestion AJAX ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    function traiterUploadFichier($champ, $types_acceptes, $dossier = "docs/") {
        header('Content-Type: application/json');

        if (!isset($_FILES[$champ])) return;

        if (!is_dir($dossier)) mkdir($dossier, 0775, true);

        $fichier = $_FILES[$champ];

        if ($fichier['error'] !== 0) {
            echo json_encode(["success" => false, "message" => "Erreur d'upload."]);
            exit;
        }

        file_put_contents('../logs/error.log', "Champ reçu : $champ\n", FILE_APPEND);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $typeMime = finfo_file($finfo, $fichier['tmp_name']);
        finfo_close($finfo);
        file_put_contents('../logs/error.log', "Type MIME détecté : $typeMime\n", FILE_APPEND);


        if (!in_array($typeMime, $types_acceptes)) {
            echo json_encode(["success" => false, "message" => "Type de fichier non autorisé."]);
            exit;
        }

        $nom_original = basename($fichier['name']);
        $nom_securise = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $nom_original);
        $utilisateur_id = $_SESSION['id'];
        $destination = $dossier . $utilisateur_id . "_" . $nom_securise;

        if (move_uploaded_file($fichier['tmp_name'], $destination)) {
            file_put_contents('../logs/error.log', "Fichier enregistré à : $destination\n", FILE_APPEND);
            echo json_encode(["success" => true, "url" => $destination]);
        } else {
            echo json_encode("Impossible de sauvegarder le fichier.");
        }
        exit;
    }


    // Traitement upload PDF
    if (isset($_FILES['pdf'])) {
        traiterUploadFichier('pdf', ['application/pdf']);
    }

    // Traitement upload vidéo
    if (isset($_FILES['mp4'])) {
        traiterUploadFichier('mp4', ['video/mp4']);
    }

    // ✅ Sauvegarde Markdown
    if (isset($_POST['markdown'])) {
        header('Content-Type: application/json');

        $timestamp = time();
        $contenu = $_POST['markdown'];
        $utilisateur_id = $_SESSION['id'];
        $date = date("d/m/Y H:i:s", $timestamp);

        if (!is_dir("cours")) {
            mkdir("cours", 0775, true);
        }

        $nom = isset($_POST['nom']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_POST['nom']) : time();
        $nomDossier = "cours/$utilisateur_id";
        if (!is_dir($nomDossier)) mkdir($nomDossier, 0775, true);
        $nomFichier = "$nomDossier/{$nom}.md";
        $titre = "Cours du " . date("d/m/Y H:i:s", $timestamp);

        $sauvegardeOK = file_put_contents($nomFichier, $contenu);

        if ($sauvegardeOK !== false) {
            // --- Mise à jour du XML ---
            if (!isset($xml->cours)) {
                $xml->addChild('cours');
            }

            $cour = $xml->cours->addChild('cour');
            $cour->addChild('titre', $titre);
            $cour->addChild('fichier', basename($nomFichier));
            $cour->addChild('utilisateur_id', $utilisateur_id);
            $cour->addChild('date_modification', $date);

            $contenuNode = $cour->addChild('contenu');
            $dom = dom_import_simplexml($contenuNode);
            $dom->appendChild($dom->ownerDocument->createCDATASection($contenu));

            if (file_exists($xml_path)) {
                copy($xml_path, $xml_path . '.backup_' . time());
            }
            $xml->asXML($xml_path);

            // --- Insertion en base SQL (table `cours`) ---
            if (isset($pdo)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO cours (titre, fichier, date_modification, contenu, user_id)
                        VALUES (:titre, :fichier, :date_modification, :contenu, :user_id)
                    ");
                    $stmt->execute([
                        ':titre'            => $titre,
                        ':fichier'          => basename($nomFichier),
                        ':date_modification'=> $date,
                        ':contenu'          => mb_substr($contenu, 0, 200),
                        ':user_id'          => $utilisateur_id,
                    ]);
                } catch (PDOException $e) {
                    error_log("Erreur insertion cours SQL : " . $e->getMessage());
                }
            }

            echo json_encode(["success" => true, "message" => "Fichier sauvegardé et base mise à jour."]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur d'écriture du fichier."]);
        }
        exit;
    }
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cours Professeurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <script src="javascript.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<header id="header">
    <h2 style="margin: 0px;">Ajouter un Cours</h2>
</header>
<body>
    <a href="interface.php" class="as" style="margin: 0px 1vw;"><img src="../images/retour.png" id="retour"> Retour</a>

    <label for="nomCours" style="margin: 0px 1vw;">Titre du cours (sans extension) :</label>

    <input type="text" id="nomCours" placeholder="ex: cours_01" style="width: 15vw;">

    <div class="main" style="display: flex;">
        <div style="max-width: 100%;">
            <div class="toolbar">
                <button onclick="addMarkdown('**', '**')"><img src="../images/gras.png" title="Mettre en gras"></button>
                <button onclick="addMarkdown('*', '*')"><img src="../images/italique.png" title="Mettre en italique"></button>
                <button onclick="addMarkdown('![Texte Alt](URL)', '')"><img src="../images/image.webp" title="Ajouter une image"></button>
                <button onclick="addMarkdown('[Texte du lien](URL)', '')"><img src="../images/lien.webp" title="Ajouter un lien"></button>
                <button onclick="addMarkdown('```\n', '\n```')"><img src="../images/code.webp" title="Ajouter du code brut"></button>
                <button onclick="addMarkdown('# ', '')"><img src="../images/titre.png" title="Ajouter un Titre"></button>
                <label style="cursor: pointer;">
                    <img src="../images/pdf.webp" title="Téléverser un PDF" style="height: 25px;">
                    <input type="file" id="uploadPDFInput" accept="application/pdf" style="display: none;">
                </label>
                <label style="cursor: pointer;">
                    <img src="../images/video.webp" title="Téléverser une vidéo" style="height: 25px;">
                    <input type="file" id="uploadVIDEOInput" accept="video/mp4" style="display: none;">
                </label>
                <button onclick="sauvegarderMarkdown()"><img src="../images/sauvegarder.png" title="Sauvegarder"></button>
            </div>
<textarea id="editor" oninput="renderMarkdown()">
# Bienvenue

Écris ici ton cours en Markdown.
</textarea>
        </div>
        <div id="preview"></div>
    </div>

    <div class="afficher_plus">
        <a href="liste_cours.php">Afficher plus</a>
    </div>
</body>
</html>
