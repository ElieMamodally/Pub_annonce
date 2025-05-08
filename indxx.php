<!-- db.php -->
<?php
$host = "localhost";
$dbname = "magis_pub";
$username = "root"; // Modifier si nécessaire
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!-- header.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Réseau Social</title>
    <link rel="stylesheet" href="assets/css/styles.css"> 
    <script src="http://localhost:3000/socket.io/socket.io.js"></script>
    <script src="assets/js/script.js" defer></script>



</head>
<body>
    <header>
        <nav>
            <a href="newsfeed.php">Fil d'actualité</a>
            <a href="profile.php">Mon Profil</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>
    <main>



<!-- footer.php -->
 
<footer>
        <p>&copy; 2025 Mon Réseau Social. Tous droits réservés.</p>
    </footer>
</main>
</body>
</html>

<!-- newsfeed.php -->
<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

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
    <div class="post">
        <div class="post-header">
            <a href="mur.php?id=<?= htmlspecialchars($post['user_id']) ?>">
                <img src="uploads/<?= $pdp ?>" class="pdp" alt="<?= $username ?>'s profile picture">
                <h3><?= $username ?></h3>
            </a>
            <div class="opt">
                <div class="menu">
                    <small><?= date("d M Y H:i", strtotime($post["created_at"])) ?></small>
                    <?php if ($_SESSION["user_id"] == $post["user_id"]): ?>
                        <div class="post-menu">
                            <button class="menu-btn">⋮</button>
                            <div class="menu-content">
                                <a href="edit_post.php?id=<?= $post['id'] ?>">Modifier</a>
                                <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Supprimer cette publication ?')">Supprimer</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="nbr_photo">
                    <span class="image-count"><?= $image_count ?> photo<?= $image_count > 1 ? 's' : '' ?></span>
                </div>
            </div>
        </div>
        
        <p><?= htmlspecialchars($post["content"]) ?></p>
        
        <?php if (!empty($images)): ?>
            <div class="post-slider">
                <div class="slider-container">
                    <?php foreach ($images as $image): ?>
                        <img class="slider-image" src="uploads/<?= htmlspecialchars($image) ?>">
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $likes_stmt->execute([$post["id"]]);
        $likes_count = $likes_stmt->fetchColumn();
        $user_like_stmt->execute([$post["id"], $_SESSION["user_id"]]);
        $user_has_liked = $user_like_stmt->fetch();
        ?>
        
        <form action="like_post.php" method="POST">
            <input type="hidden" name="post_id" value="<?= $post["id"] ?>">
            <button type="submit" id="like" class="<?= $user_has_liked ? 'liked' : '' ?>">
                ❤️ <?= $likes_count ?>
            </button>
        </form>
        
        <div class="comments">
            <?php
            $comments_stmt->execute([$post["id"]]);
            $comments = $comments_stmt->fetchAll();
            foreach ($comments as $comment):
                $comment_username = htmlspecialchars($comment["username"] ? $comment["username"]: "Utilisateur inconnu");
                $comment_pdp = htmlspecialchars($comment["pdp"] ? $comment["pdp"] : "default.png"); 
            ?>
                <div class="comment">
                    <img src="uploads/<?= $comment_pdp ?>" class="pdp">
                    <strong><?= $comment_username ?>:</strong>
                    <p><?= htmlspecialchars($comment["content"]) ?></p>
                    <small><?= date("d M Y H:i", strtotime($comment["created_at"])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="comment-form" action="add_comment.php" method="POST">
            <input type="hidden" name="post_id" value="<?= $post["id"] ?>">
            <input class="comment-text" type="text" name="comment" placeholder="Ajouter un commentaire..." required>
            <button class="comment-btn" type="submit">Envoyer</button>
        </form>
    </div>
<?php endforeach;?>

<?php 
include 'includes/footer.php';?>

<!--add_comment.php  -->
<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"]) || !isset($_POST["post_id"]) || !isset($_POST["comment"])) {
    exit("Accès non autorisé");
}

$post_id = $_POST["post_id"];
$user_id = $_SESSION["user_id"];
$comment = trim($_POST["comment"]);

if (!empty($comment)) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$post_id, $user_id, $comment]);
}

header("Location: newsfeed.php");
exit();
?>
<!-- delete_post.php  -->
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


<!-- edit_post -->
<?php
session_start();
include 'includes/db.php';
require 'includes/header.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Vérifier si l'ID du post est présent dans l'URL
if (!isset($_GET["id"])) {
    header("Location: newsfeed.php");
    exit();
}

$post_id = $_GET["id"];
$user_id = $_SESSION["user_id"];

// Récupérer les infos du post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

// Si le post n'existe pas ou ne correspond pas à l'utilisateur, redirection
if (!$post) {
    header("Location: newsfeed.php");
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST["content"];
    $images = $_FILES["images"];

    // Gérer les images (si nouvelles images uploadées)
    $uploaded_images = [];
    if (!empty($images["name"][0])) {
        foreach ($images["tmp_name"] as $key => $tmp_name) {
            $file_name = time() . "_" . basename($images["name"][$key]);
            $target_path = "uploads/" . $file_name;

            if (move_uploaded_file($tmp_name, $target_path)) {
                $uploaded_images[] = $file_name;
            }
        }

        // Mettre à jour la publication avec les nouvelles images
        $image_paths = implode(",", $uploaded_images);
        $stmt = $pdo->prepare("UPDATE posts SET content = ?, images = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$content, $image_paths, $post_id, $user_id]);
    } else {
        // Mise à jour du texte uniquement
        $stmt = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$content, $post_id, $user_id]);
    }

    header("Location: newsfeed.php");
    exit();
}
?>

<h2>Modifier la publication</h2>
<form action="" method="POST" enctype="multipart/form-data">
    <label for="content">Texte :</label>
    <textarea name="content" required><?= htmlspecialchars($post["content"]) ?></textarea>

    <label for="images">Modifier les images :</label>
    <input type="file" name="images[]" multiple accept="image/*">

    <!-- Affichage des images existantes -->
    <div class="existing-images">
        <h4>Images actuelles :</h4>
        <?php
        $existing_images = explode(",", $post["images"]);
        foreach ($existing_images as $image) {
            if (!empty($image)) {
                echo "<img src='uploads/$image' width='100' style='margin:5px;'>";
            }
        }
        ?>
    </div>

    <button type="submit">Mettre à jour</button>
</form>

<?php require 'includes/footer.php'; ?>

<!-- follow.php -->
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

<!-- get_notification.php -->
<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    exit(); // Ne pas continuer si l'utilisateur n'est pas connecté
}

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND seen = FALSE ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);
?>
<!-- index.php -->
<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        header("Location: newsfeed.php");
        exit();
    } else {
        echo "Email ou mot de passe incorrect.";
    }
}
?>
<div class="logo">
    <img src="./assets/logo/magis" alt="">
</div>
<form class="form-index" method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">Se connecter</button>
    <p>Vous n'avez pas de compte? <a href="register.php">S'inscrire</a></p>
</form>


<!-- like_post.php -->
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
<!-- logout.php -->
<?php
session_start();
session_destroy();
header("Location: index.php");
exit();
?>

<!-- mur.php -->
<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

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
<img src="uploads/<?= htmlspecialchars($user['pdp']) ?>" alt="Photo de profil de <?= htmlspecialchars($user['username']) ?>">



<h1>Publications</h1>

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
    // Vérifier si 'pdp' et 'username' existent
    $username = htmlspecialchars(isset($post["username"]) ? $post["username"] : "Utilisateur inconnu");
    $pdp = htmlspecialchars(isset($post["pdp"]) ? $post["pdp"] : "default.png");

    // image publié
    $images = json_decode($post["images"], true);
    $image_count = count($images);
?>
    <div class="post">
        <div class="post-header">
            <a href="mur.php?id=<?= htmlspecialchars($post['user_id']) ?>">
                <img src="uploads/<?= $pdp ?>" class="pdp" alt="<?= $username ?>'s profile picture">
                <h3><?= $username ?></h3>
            </a>
            <div class="opt">
                <div class="menu">
                    <small><?= date("d M Y H:i", strtotime($post["created_at"])) ?></small>
                    <?php if ($_SESSION["user_id"] == $post["user_id"]): ?>
                        <div class="post-menu">
                            <button class="menu-btn">⋮</button>
                            <div class="menu-content">
                                <a href="edit_post.php?id=<?= $post['id'] ?>">Modifier</a>
                                <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Supprimer cette publication ?')">Supprimer</a>
                            </div>
                        </div>
                        
                    <?php endif; ?>
                </div>
                <div class="nbr_photo">
                    <!-- Indicateur du nombre d'images -->
                    <span class="image-count"><?= $image_count ?> photo<?= $image_count > 1 ? 's' : '' ?></span>
                </div>
            </div>
        </div>
        
        <p><?= htmlspecialchars($post["content"]) ?></p>
        
        <?php 
if (!empty($images)): 
?>
    <div class="post-slider">
        <div class="slider-container">
            <?php foreach ($images as $image): ?>
                <img class="slider-image" src="uploads/<?= htmlspecialchars($image) ?>">
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

        
        <?php
        $likes_stmt->execute([$post["id"]]);
        $likes_count = $likes_stmt->fetchColumn();

        $user_like_stmt->execute([$post["id"], $_SESSION["user_id"]]);
        $user_has_liked = $user_like_stmt->fetch();
        ?>
        
        <form action="like_post.php" method="POST">
            <input type="hidden" name="post_id" value="<?= $post["id"] ?>">
            <button type="submit" id="like" class="<?= $user_has_liked ? 'liked' : '' ?>">
                ❤️ <?= $likes_count ?>
            </button>
        </form>
        
        <div class="comments">
            <?php
            $comments_stmt->execute([$post["id"]]);
            $comments = $comments_stmt->fetchAll();
            foreach ($comments as $comment):
                $comment_username = htmlspecialchars(isset($comment["username"]) ? $comment["username"] : "Utilisateur inconnu");
                $comment_pdp = htmlspecialchars(isset($comment["pdp"]) ? $comment["pdp"] : "default.png");
            ?>
                <div class="comment">
                    <img src="uploads/<?= $comment_pdp ?>" class="pdp">
                    <strong><?= $comment_username ?>:</strong>
                    <p><?= htmlspecialchars($comment["content"]) ?></p>
                    <small><?= date("d M Y H:i", strtotime($comment["created_at"])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="comment-form" action="add_comment.php" method="POST">
            <input type="hidden" name="post_id" value="<?= $post["id"] ?>">
            <input class="comment-text" type="text" name="comment" placeholder="Ajouter un commentaire..." required>
            <button class="comment-btn" type="submit">Envoyer</button>
        </form>
    </div>
<?php endforeach; ?>

<script src="assets/js/script.js"></script>

<?php include 'includes/footer.php'; ?>

<!-- post.php -->
<?php
include 'includes/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT username, pdp FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!isset($_SESSION["user_id"])) {
    die("Accès refusé");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = trim($_POST["content"]);
    $user_id = $_SESSION["user_id"];
    $imageNames = [];

    if (!empty($_FILES["images"]["tmp_name"][0])) {
        foreach ($_FILES["images"]["tmp_name"] as $key => $tmp_name) {
            $imageName = time() . "_" . $_FILES["images"]["name"][$key];
            move_uploaded_file($tmp_name, "uploads/" . $imageName);
            $imageNames[] = $imageName;
        }
    }

    $imagesJSON = json_encode($imageNames);
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, images) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $imagesJSON]);

    // Envoyer une notification en temps réel via WebSocket
    $postData = [
        "username" => $user['username'],
        "content" => $content
    ];

    $socketData = json_encode($postData);
    
    $socket = fsockopen("localhost", 3000, $errno, $errstr, 30);
    if ($socket) {
        fwrite($socket, $socketData);
        fclose($socket);
    }

    header("Location: newsfeed.php");
    exit();
}
?>


<!-- Affichage de la photo de profil et du nom de l'utilisateur -->
<div class="post-box">
    <div class="user-info">
    <img src="uploads/<?php echo htmlspecialchars(isset($user['pdp']) ? $user['pdp'] : 'default.png'); ?>" alt="Profil" class="pdp">
<span><?php echo htmlspecialchars(isset($user['username']) ? $user['username'] : 'Utilisateur inconnu'); ?></span>

    </div>

<!-- profiles.php -->
<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["username"])) {
        $new_username = trim($_POST["username"]);
        $pdo->prepare("UPDATE users SET username = ? WHERE id = ?")->execute([$new_username, $user_id]);
        $_SESSION["username"] = $new_username;
    }

    if (!empty($_FILES["pdp"]["name"])) {
        $pdp_name = time() . "_" . $_FILES["pdp"]["name"];
        move_uploaded_file($_FILES["pdp"]["tmp_name"], "uploads/" . $pdp_name);
        $pdo->prepare("UPDATE users SET pdp = ? WHERE id = ?")->execute([$pdp_name, $user_id]);
    }

    if (!empty($_FILES["pdc"]["name"])) {
        $pdc_name = time() . "_" . $_FILES["pdc"]["name"];
        move_uploaded_file($_FILES["pdc"]["tmp_name"], "uploads/" . $pdc_name);
        $pdo->prepare("UPDATE users SET pdc = ? WHERE id = ?")->execute([$pdc_name, $user_id]);
    }

    header("Location: profile.php");
    exit();
}
?>

<div class="profile">
    <div class="cover">
        <img src="uploads/<?= htmlspecialchars($user["pdc"]) ?>" class="pdc">
    </div>
    <div class="pdp-container">
        <img src="uploads/<?= htmlspecialchars($user["pdp"]) ?>" class="pdp">
    </div>
    <h1><?= htmlspecialchars($user["username"]) ?></h1>

    <form class="profile-form" method="POST" enctype="multipart/form-data">
        <label>Modifier le nom :</label>
        <input type="text" name="username" placeholder="<?= htmlspecialchars($user["username"]) ?>">
        
        <label>Changer la photo de profil :</label>
        <input type="file" name="pdp">
        
        <label>Changer la photo de couverture :</label>
        <input type="file" name="pdc">
        
        <button type="submit">Enregistrer</button>
    </form>
</div>

<?php
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>

<!-- register -->
<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $email, $password])) {
        $_SESSION["user_id"] = $pdo->lastInsertId();
        header("Location: newsfeed.php");
        exit();
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>
<form method="POST">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>

<style>
    
*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
}

body{
    display: flex;
    flex-direction: column;
    align-items: center;
}
header{
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 99;
}
nav{
    display: flex;
    height: 80px;
    justify-content: space-around;
    align-items: center;
    background-color: #A36361;
}

header nav a {
    color: white;
    margin: 0 15px;
    text-decoration: none;
    font-weight: bold;
}

header nav a:hover {
    text-decoration: underline;
}
main{
    display: flex;
    flex-direction: column;
    width:700px ;
    height: 100vh;
    padding: 40px;
    box-shadow: inset 0 0 12px grey;
    overflow-y: auto;
    scrollbar-width: none;
    margin-top: 80px;
    align-items: center;

}
@media (max-width:480px) {
    header{
        width: 100%;
    }
    nav a{
        font-size: .8rem;
    }
    main{
        max-width: 100%;
    }
}

@media (max-width:700px) {
    header{
        width: 100%;
    }
    nav a{
        font-size: .8rem;
    }
    main{
        max-width: 100%;
    }
}
button{
    height: 50px;
    border: none;
    border-radius: 5px;
    background-color: #d7d7d7;
}
Button:hover{
    background-color: #f39391;
}
/* Post */
.publier-form{
    display: flex;
    flex-direction: column;
    
}

/* Newsfeed */
img{
    width: 100%;
}
.post {
    display: flex;
    flex-direction: column;
    border: 1px solid #ccc;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    background-color: rgb(204, 204, 204,0.3);
}
.post-header{
    display: flex;
    justify-content: space-between;
}

.post-header a{
    display: flex;
    align-items: center;
    text-decoration: none;
    color: black;
}
.post-header .opt{
    display: flex;
    flex-direction: column;
    align-items: center;
}
.post-header .opt .nbr_photo{
    background-color: rgb(201, 201, 201);
    padding: 5px;
    border-radius: 20px;
    color: #fff;
}

.post-menu {
    position: relative;
    display: inline-block;
}

.menu-btn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: black;
}

.menu-content {
    display: none;
    position: absolute;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
    z-index: 99;
}

.menu-content a {
    display: block;
    padding: 5px 10px;
    text-decoration: none;
    color: black;
}

.menu-content a:hover {
    background: #ddd;
}

.post-menu:hover .menu-content {
    display: block;
}
²
.post-slider {
    display: flex;
}

.slider-container {
    display: flex;
    overflow: scroll;
    scrollbar-width: none;
}

.slider-image {
    width: 100%;
    object-fit: cover;
    padding: 10px;
    transition: transform 0.5s ease-in-out;
}
.pdp {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}
/* Commentaires */
.comments {
    margin-top: 20px;
    padding-left: 20px;
}

.comment {
    margin-bottom: 10px;
}

.comment img {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin-right: 10px;
}

.comment strong {
    font-weight: bold;
}

.comment-form{
    display: flex;
    width: 100%;
    align-items: end;
}
.comment-text{
    height: 40px;
    margin-right: 10px;
}
.comment-btn{
    width: 150px;
    height: 40px;
    background-color:#118AB2 ;
    color: #fff;
}
.comment-btn:hover{
    background-color:#11a3d3 ;
    color: #fff;
}
#like{
    width: 100px;
}
button.liked {
    background-color: #fa685e;
    width: 100px;
    color: #ffffff;
}
button.liked:hover {
    background-color: #e53935;
    color: #ffffff;
    
}

/* index */
.form-index{
    display: flex;
    gap: 10px;
    width: 300px;
    height: 200px;
    padding: 20px;
    flex-direction: column;
    align-items: center;
    box-shadow: inset 0 0 4px black;
    border-radius: 10px;

}
.form-index input{
    width: 100%;
    height: 30px;
    padding: 7px;
}
.form-index button{
    background-color: #E8B298;
    color: white;
    width: 100%;
}
.form-index button:hover{
    background-color: #e38b5e;
}
/* Post */

.publier-form {
    display: flex;
    width: 100%;
    flex-direction: column;
}

.publier-form textarea, .profile-form input:nth-of-type(1) {
    width: 100%;
    border: none;
    resize: none;
    padding: 10px;
    border-radius: 5px;
    outline: none;
    background: #f5f5f5;
    font-size: 14px;
}

.publier-form button, .profile-form button {
    display: block;
    width: 100%;
    margin-top: 10px;
    padding: 10px;
    background: #1877F2;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.post-box {
    background: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.user-info {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}

.user-info .pdp {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}
.image-count{
    position: relative;;
}

/* PROFILE */

.profile {
    text-align: center;
    margin: 20px;
}

.profile .cover {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.profile .pdc {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile .pdp{
    width: 120px;
    height: 120px;
    object-fit: cover;
}
.pdp-container {
    position: relative;
    top: -50px;
}



.profile-form{
    margin-top: 50px;
    display: flex;
    flex-direction: column;
}

.profile-form button

#notification-list {
    list-style-type: none;
    padding: 0;
    margin: 0;
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ccc; 
    border-radius: 5px;
    background-color: #f9f9f9;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

#notification-list li {
    padding: 10px; 
    border-bottom: 1px solid #eee;
    transition: background-color 0.3s;
}

#notification-list li:hover {
    background-color: #f1f1f1;
}

#notification-list li small {
    color: #888; 
}


</style>