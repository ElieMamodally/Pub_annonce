<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_GET["id"])) {
    header("Location: newsfeed.php");
    exit();
}

$comment_id = (int)$_GET["id"];
$user_id = (int)$_SESSION["user_id"];

// Vérifier que l'utilisateur est bien l'auteur du commentaire
$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$comment_id, $user_id]);
$comment = $stmt->fetch();

if ($comment) {
    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$comment_id]);
}

header("Location: newsfeed.php");
exit();
?>