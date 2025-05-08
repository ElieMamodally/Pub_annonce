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
    $content = $_POST["content"];
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

    // Après avoir inséré la publication
    $post_id = $pdo->lastInsertId();

    // Récupérer tous les utilisateurs sauf l'auteur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id != ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $users = $stmt->fetchAll();

    // Récupérer le nom d'utilisateur de l'auteur
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $author = $stmt->fetch();

    // Envoyer une notification à chaque utilisateur
    foreach ($users as $user) {
        // Vérifier si l'utilisateur a déjà une notification non lue pour une publication récente
        $stmt = $pdo->prepare("SELECT id FROM notifications WHERE user_id = ? AND post_id = ? AND seen = 0");
        $stmt->execute([$user["id"], $post_id]);
        $existing_notification = $stmt->fetch();

        if ($existing_notification) {
            // Mettre à jour la notification existante
            $message = $author["username"] . " a publié une nouvelle mise à jour : " . $content;
            $stmt = $pdo->prepare("UPDATE notifications SET message = ? WHERE id = ?");
            $stmt->execute([$message, $existing_notification["id"]]);
        } else {
            // Créer une nouvelle notification
            $message = $author["username"] . " a publié : " . $content;
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, post_id, message) VALUES (?, ?, ?)");
            $stmt->execute([$user["id"], $post_id, $message]);
        }
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