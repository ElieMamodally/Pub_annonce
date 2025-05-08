<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    exit(); // Ne pas continuer si l'utilisateur n'est pas connecté
}

$data = json_decode(file_get_contents('php://input'), true);
if (isset($data['notification_id'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET seen = TRUE WHERE id = ?");
    $stmt->execute([$data['notification_id']]);
}
?>
