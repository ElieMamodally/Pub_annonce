<?php
try {
    $pdo = new PDO(
        "mysql:host=" . getenv("DB_HOST") . ";dbname=" . getenv("DB_NAME") . ";charset=utf8",
        getenv("DB_USER"),
        getenv("DB_PASS")
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
