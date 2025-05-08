<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
include 'includes/nav.php';

if (!isset($_SESSION["user_id"]) || !isset($_GET["id"])) {
    header("Location: newsfeed.php");
    exit();
}

$comment_id = (int)$_GET["id"];
$user_id = (int)$_SESSION["user_id"];

// Récupérer le commentaire
$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$comment_id, $user_id]);
$comment = $stmt->fetch();

if (!$comment) {
    header("Location: newsfeed.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = trim($_POST["content"]);

    if (!empty($content)) {
        $pdo->prepare("UPDATE comments SET content = ? WHERE id = ?")->execute([$content, $comment_id]);
        header("Location: newsfeed.php");
        exit();
    }
}
?>

<!-- Formulaire de modification -->
<h2>Modifier le commentaire</h2>
<form method="POST">
    <textarea name="content" required><?= htmlspecialchars($comment["content"]) ?></textarea>
    <button type="submit">Enregistrer</button>
</form>