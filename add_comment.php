<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["post_id"]) || !isset($_POST["comment"])) {
    exit("Accès non autorisé.");
}

$post_id = (int)$_POST["post_id"];
$user_id = (int)$_SESSION["user_id"];
$comment = trim($_POST["comment"]);
$parent_id = isset($_POST["parent_id"]) ? (int)$_POST["parent_id"] : null;

if (!empty($comment)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at, parent_id) VALUES (?, ?, ?, NOW(), ?)");
        $stmt->execute([$post_id, $user_id, $comment, $parent_id]);
    } catch (PDOException $e) {
        error_log("Erreur lors de l'ajout du commentaire : " . $e->getMessage());
    }
}
echo('commentaire ajouter');
header("Location: newsfeed.php");
exit();
?>