<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    exit("Accès non autorisé");
}

$user_id = $_SESSION["user_id"];
$content = "Vous avez un nouvel abonné!"; // Contenu de la notification (à personnaliser)

$stmt = $pdo->prepare("INSERT INTO notifications (user_id, content) VALUES (?, ?)");
$stmt->execute([$user_id, $content]);

header("Location: newsfeed.php");
exit();
?>
