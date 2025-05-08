<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["post_id"])) {
    exit("Accès non autorisé");
}

$post_id = $_POST["post_id"];
$user_id = $_SESSION["user_id"];

$stmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$like = $stmt->fetch();

if ($like) {
    $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?")->execute([$post_id, $user_id]);
} else {
    $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)")->execute([$post_id, $user_id]);
}

header("Location: newsfeed.php");
exit();
?>
