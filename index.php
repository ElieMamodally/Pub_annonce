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
<div class="logincontent">
    <div class="logo">
        <video src="./assets/logo/VideoLogo.mp4" autoplay muted></video>
    </div>
    <form class="form-index" method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
        <p>Vous n'avez pas de compte? <a href="register.php"> S'inscrire</a></p>
    </form>
</div>
<?php
include 'includes/footer.php';
?>