<?php
// $host = "localhost";
// $dbname = "magis_pub";
// $username = "root"; // Modifier si nécessaire
// $password = "";
$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$db   = getenv("DB_NAME");

$conn = new mysqli($host, $user, $pass, $db);
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Journalisation des erreurs
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Une erreur est survenue. Veuillez réessayer plus tard.");
}
?>
