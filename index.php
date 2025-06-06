<?php
require 'vendor/autoload.php';

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Dotenv\Dotenv;

// Charger les variables d'environnement depuis .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Accéder aux variables d'environnement
$accountName = $_ENV['AZURE_ACCOUNT_NAME'];
$accountKey = $_ENV['AZURE_ACCOUNT_KEY'];
$containerName = $_ENV['AZURE_CONTAINER_NAME'];

$connectionString = "DefaultEndpointsProtocol=https;AccountName=noeltheostockage;AccountKey=BsY1hen4QgxqoPDYSKHam7J79XQL45A6xy6DE3knrxOGcQeUYEnak55r3LOnlTfTnG/PoyWlShTm+AStn1mEiw==;EndpointSuffix=core.windows.net";
$blobClient = BlobRestProxy::createBlobService($connectionString);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = basename($file['name']);
    echo "Fichier reçu : $filename<br>";

    try {
        $content = fopen($file['tmp_name'], "r");
        $blobClient->createBlockBlob($containerName, $filename, $content);
        $blobUrl = "https://$accountName.blob.core.windows.net/$containerName/$filename";

        $stmt = $pdo->prepare('INSERT INTO fichiers (nom, chemin, taille) VALUES (?, ?, ?)');
        $stmt->execute([$filename, $blobUrl, $file['size']]);

        $message = "✅ Fichier uploadé avec succès sur Azure Blob Storage.";
    } catch (ServiceException $e) {
        $message = "Erreur Azure : " . $e->getCode() . " - " . $e->getMessage();
    } catch (Exception $e) {
        error_log("Erreur générale : " . $e->getMessage());
        $message = "❌ Erreur inattendue : " . $e->getMessage();
    }
}

try {
    $fichiers = $pdo->query('SELECT * FROM fichiers ORDER BY date_upload DESC')->fetchAll();
} catch (Exception $e) {
    error_log("Erreur base de données : " . $e->getMessage());
    $fichiers = [];
    $message = "❌ Erreur lors de la récupération des fichiers.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Upload vers Azure</title>
</head>

<body>
    <h1>Uploader un fichier</h1>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Envoyer</button>
    </form>

    <?php if (!empty($message)) : ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <h2>Fichiers enregistrés :</h2>
    <ul>
        <?php foreach ($fichiers as $fichier) : ?>
            <li>
                <a href="<?= htmlspecialchars($fichier['chemin']) ?>" target="_blank"><?= htmlspecialchars($fichier['nom']) ?></a>
                (<?= round($fichier['taille'] / 1024, 2) ?> Ko)
            </li>
        <?php endforeach; ?>
    </ul>
</body>

</html>