<?php
$host = "localhost";
$dbname = "magis_pub";
$username = "root"; // Modifier si nécessaire
$password = "";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Journalisation des erreurs
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Une erreur est survenue. Veuillez réessayer plus tard.");
}
?>