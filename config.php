<?php
$host = 'mysqlmatthieugeiss.mysql.database.azure.com';
$port = 3306;
$dbname = 'theonoelmysql'; // ton nom de base
$username = 'b3dev2025';
$password = 'MyDigitalSchool2025'; // mets ton mot de passe réel

$ssl_ca = __DIR__ . '/BaltimoreCyberTrustRoot.crt.pem'; // certificat CA de Azure MySQL, à télécharger (explications plus bas)

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // optionnel
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Connexion MySQL Azure OK<br>";
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
