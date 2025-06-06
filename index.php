<?php
require 'vendor/autoload.php'; // charge le SDK Azure

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'config.php'; // ta config PDO et autre

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

// Connexion au blob storage Azure
$connectionString = "DefaultEndpointsProtocol=https;AccountName=TON_ACCOUNT_NAME;AccountKey=TON_ACCOUNT_KEY;EndpointSuffix=core.windows.net";
$containerName = "uploads";

$blobClient = BlobRestProxy::createBlobService($connectionString);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = basename($file['name']);

    try {
        // Ouvre le fichier temporaire pour lecture
        $content = fopen($file['tmp_name'], "r");

        // Upload du fichier vers Azure Blob Storage
        $blobClient->createBlockBlob($containerName, $filename, $content);

        // Stocke l'URL publique ou URL avec SAS si nécessaire
        $blobUrl = "https://noeltheostockage_1749126671563.blob.core.windows.net/$containerName/$filename";

        // Enregistre dans ta base de données
        $stmt = $pdo->prepare('INSERT INTO fichiers (nom, chemin, taille) VALUES (?, ?, ?)');
        $stmt->execute([
            $filename,
            $blobUrl,
            $file['size']
        ]);

        $message = "Fichier uploadé avec succès sur Azure Blob Storage.";

    } catch(ServiceException $e) {
        $message = "Erreur lors de l'upload sur Azure : " . $e->getMessage();
    }
}

// Récupération des fichiers de la BDD comme avant
$fichiers = $pdo->query('SELECT * FROM fichiers ORDER BY date_upload DESC')->fetchAll();
?>
