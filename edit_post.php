<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
include 'includes/nav.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: newsfeed.php");
    exit();
}

$post_id = (int)$_GET["id"];
$user_id = (int)$_SESSION["user_id"];

// Récupérer la publication actuelle
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: newsfeed.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = $_POST["content"];
    $images = $_FILES["images"];

    // Gérer les nouvelles images
    $uploaded_images = [];
    if (!empty($images["name"][0])) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5 Mo

        foreach ($images["tmp_name"] as $key => $tmp_name) {
            if (in_array($images["type"][$key], $allowed_types) && $images["size"][$key] <= $max_size) {
                $file_name = time() . "_" . basename($images["name"][$key]);
                move_uploaded_file($tmp_name, "uploads/" . $file_name);
                $uploaded_images[] = $file_name;
            } else {
                die("Type de fichier non autorisé ou taille trop grande.");
            }
        }

        // Supprimer les anciennes images
        $existing_images = json_decode($post["images"], true);
        foreach ($existing_images as $old_image) {
            if (file_exists("uploads/" . $old_image)) {
                unlink("uploads/" . $old_image);
            }
        }

        $imagesJSON = json_encode($uploaded_images);
        $stmt = $pdo->prepare("UPDATE posts SET content = ?, images = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$content, $imagesJSON, $post_id, $user_id]);
    } else {
        // Mettre à jour uniquement le texte
        $stmt = $pdo->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$content, $post_id, $user_id]);
    }

    header("Location: newsfeed.php");
    exit();
}
?>

<!-- Formulaire de modification -->
<h2>Modifier la publication</h2>
<form action="" method="POST" enctype="multipart/form-data">
    <label for="content">Texte :</label>
    <textarea name="content" required><?= htmlspecialchars($post["content"]) ?></textarea>

    <label for="images">Modifier les images :</label>
    <input type="file" name="images[]" multiple accept="image/*">

    <!-- Afficher les images existantes -->
    <div class="existing-images">
        <h4>Images actuelles :</h4>
        <?php
        $existing_images = json_decode($post["images"], true);
        foreach ($existing_images as $image) {
            if (!empty($image)) {
                echo "<img src='uploads/$image' width='100' style='margin:5px;'>";
            }
        }
        ?>
    </div>

    <button type="submit">Mettre à jour</button>
</form>

<?php include 'includes/footer.php'; ?>