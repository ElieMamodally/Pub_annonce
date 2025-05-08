<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    echo json_encode(['count' => 0]);
    exit();
}

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND seen = 0");
$stmt->execute([$user_id]);
$count = $stmt->fetchColumn();

echo json_encode(['count' => $count]);
?>