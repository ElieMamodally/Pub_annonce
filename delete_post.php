<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_GET["id"])) {
    header("Location: newsfeed.php");
    exit();
}

$post_id = $_GET["id"];
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $_SESSION["user_id"]]);

header("Location: newsfeed.php");
exit();
?>
