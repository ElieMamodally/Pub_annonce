<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["followed_id"])) {
    exit("Accès non autorisé");
}

$followed_id = $_POST["followed_id"];
$followed_by = $_SESSION["user_id"];

// Vérifier si l'utilisateur suit déjà la personne
$stmt = $pdo->prepare("SELECT * FROM followers WHERE followed_id = ? AND follower_id = ?");
$stmt->execute([$followed_id, $followed_by]);
$follow = $stmt->fetch();

if ($follow) {
    // Si déjà suivi, se désabonner
    $pdo->prepare("DELETE FROM followers WHERE followed_id = ? AND follower_id = ?")->execute([$followed_id, $followed_by]);
} else {
    // Sinon, suivre
    $pdo->prepare("INSERT INTO followers (followed_id, follower_id) VALUES (?, ?)")->execute([$followed_id, $followed_by]);
}

header("Location: profile.php");
exit();
?>
