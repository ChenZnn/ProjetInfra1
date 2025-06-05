<?php
require 'config.php';

$uploadDir = __DIR__ . '/uploads/';
$uploadUrl = 'uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = basename($file['name']);
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare('INSERT INTO fichiers (nom, chemin, taille) VALUES (?, ?, ?)');
        $stmt->execute([
            $filename,
            $uploadUrl . $filename,
            $file['size']
        ]);
        $message = "Fichier uploadé avec succès.";
    } else {
        $message = "Erreur lors de l'upload.";
    }
}

$fichiers = $pdo->query('SELECT * FROM fichiers ORDER BY date_upload DESC')->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des fichiers</title>
</head>
<body>
    <h1>Uploader un fichier</h1>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Envoyer</button>
    </form>

    <p><?= $message ?></p>

    <h2>Fichiers disponibles</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Nom</th>
            <th>Taille</th>
            <th>Date</th>
            <th>Télécharger</th>
        </tr>
        <?php foreach ($fichiers as $f): ?>
            <tr>
                <td><?= htmlspecialchars($f['nom']) ?></td>
                <td><?= round($f['taille'] / 1024, 2) ?> Ko</td>
                <td><?= $f['date_upload'] ?></td>
                <td><a href="<?= htmlspecialchars($f['chemin']) ?>" download>Télécharger</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
