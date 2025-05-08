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

<div class="logincontent">
    <div class="logo">
        <video src="./assets/logo/VideoLogo.mp4" autoplay muted></video>
    </div>
    <form class="register" method="POST">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">S'inscrire</button>
        <p><a href="index.php">J'ai déjà un compte</a></p>
    </form>
</div>
<?php
include "includes/footer.php"  
?>;