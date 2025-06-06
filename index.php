<?php

$ch = curl_init("https://www.google.com/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Erreur cURL : ' . curl_error($ch);
} else {
    echo 'Succès : cURL fonctionne.';
}
curl_close($ch);

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

echo "Chargement initial...<br>";

if (!file_exists('vendor/autoload.php')) {
    error_log("Fichier vendor/autoload.php manquant.");
    exit("Erreur technique.");
}
require 'vendor/autoload.php';
echo "Autoload OK<br>";

if (!file_exists('config.php')) {
    error_log("Fichier config.php manquant.");
    exit("Erreur technique.");
}
require 'config.php'; // suppose que $pdo est défini ici
echo "Config OK<br>";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=noeltheostockage;AccountKey=BsY1hen4QgxqoPDYSKHam7J79XQL45A6xy6DE3knrxOGcQeUYEnak55r3LOnlTfTnG/PoyWlShTm+AStn1mEiw==;EndpointSuffix=core.windows.net";
$containerName = "noeltheocontainer";

$blobClient = BlobRestProxy::createBlobService($connectionString);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = basename($file['name']);
    echo "Fichier reçu : $filename<br>";

    try {
        $content = fopen($file['tmp_name'], "r");
        $blobClient->createBlockBlob($containerName, $filename, $content);
        $blobUrl = "https://noeltheostockage.blob.core.windows.net/$containerName/$filename";

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