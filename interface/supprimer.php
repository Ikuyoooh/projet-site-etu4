<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$utilisateur_id = $_SESSION['id'];

// Active le log des erreurs
ini_set('log_errors', 1);
ini_set('display_errors', 0);
ini_set('error_log', '../logs/error.log');

$xml_path = '../Base-de-donnees/site_db.xml';
$xml = simplexml_load_file($xml_path);

// Supprimer le fichier et sa référence XML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fichier'])) {
    $nom = basename($_POST['fichier']);  // nom réel du fichier
    $chemin = "cours/" . $utilisateur_id . "/" . $nom;

    // Supprimer le fichier physique
    if (file_exists($chemin)) {
        unlink($chemin);
        error_log("Fichier supprimé : $chemin");
    } else {
        error_log("Fichier introuvable : $chemin");
    }

    // Supprimer son entrée dans le XML
    foreach ($xml->cours->cour as $index => $cour) {
        if ((string)$cour->fichier === $nom && (string)$cour->utilisateur_id === $utilisateur_id) {
            $titre = (string)$cour->titre;

            $dom = dom_import_simplexml($cour);
            if ($dom && $dom->parentNode) {
                $dom->parentNode->removeChild($dom);
                error_log("Entrée XML supprimée pour le fichier : $nom (titre: $titre)");
            }
            break;
        }
    }

    // Sauvegarder le XML
    $xml->asXML($xml_path);
}

header("Location: liste_cours.php");
exit;
