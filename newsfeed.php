<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
include 'includes/nav.php';

// Vérification de la connexion utilisateur
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Récupération des publications avec les informations utilisateur
$posts = $pdo->query("
    SELECT posts.*, users.username, users.pdp 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
")->fetchAll();
?>

<?php include 'post.php'; ?>

<!-- Formulaire de publication -->
<form class="publier-form" action="post.php" method="POST" enctype="multipart/form-data">
    <textarea name="content" placeholder="Exprimez-vous..."></textarea>
    <input type="file" name="images[]" multiple>
    <button type="submit">Publier</button>
</form>

<h1>Fil d'actualité</h1>

<?php
$likes_stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$user_like_stmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
$comments_stmt = $pdo->prepare("
    SELECT comments.*, users.username, users.pdp 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE comments.post_id = ? 
    ORDER BY comments.created_at ASC
");

foreach ($posts as $post): 
    $username = htmlspecialchars($post["username"] ? $post["username"] :"Utilisateur inconnu");
    $pdp = htmlspecialchars($post["pdp"] ? $post["pdp"] : "default.png");
    $images = json_decode($post["images"], true);
    $image_count = count($images);
?>

<?php include 'content-Pub.php' ; ?>
<?php endforeach;?>

<?php 

include 'includes/footer.php';?>
<script src="assets/js/script.js"></script>