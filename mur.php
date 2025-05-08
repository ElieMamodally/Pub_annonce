<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
include 'includes/nav.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$stmt = $pdo->prepare("SELECT posts.*, users.username, users.pdp 
                    FROM posts 
                    JOIN users ON posts.user_id = users.id 
                    WHERE posts.user_id = ? 
                    ORDER BY posts.created_at DESC");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();


if (!$user) {
    echo "Utilisateur non trouvé.";
    exit();
}
?>
<h1>Profil de <?= htmlspecialchars($user['username']) ?></h1>


<div class="cover">
    <img class="pdc" src="uploads/<?= htmlspecialchars($user['pdc']) ?>" alt="Photo de couverture de <?= htmlspecialchars($user['username']) ?>">
</div>
<div class="pdp-container">
    <img class="pdp" src="uploads/<?= htmlspecialchars($user['pdp']) ?>" alt="Photo de profil de <?= htmlspecialchars($user['username']) ?>">
</div>


<h1>Publications</h1>

<?php
$likes_stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$user_like_stmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");


foreach ($posts as $post): 
    // Vérifier si 'pdp' et 'username' existent
    $username = htmlspecialchars(isset($post["username"]) ? $post["username"] : "Utilisateur inconnu");
    $pdp = htmlspecialchars(isset($post["pdp"]) ? $post["pdp"] : "default.png");

    // image publié
    $images = json_decode($post["images"], true);
    $image_count = count($images);
?>

<?php include 'content-Pub.php' ; ?>
<?php endforeach; ?>

<script src="assets/js/script.js"></script>

<?php include 'includes/footer.php'; ?>
