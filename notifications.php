<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
include 'includes/nav.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Marquer toutes les notifications comme lues
$pdo->prepare("UPDATE notifications SET seen = 1 WHERE user_id = ?")->execute([$_SESSION["user_id"]]);

// Récupérer les notifications
$stmt = $pdo->prepare("SELECT notifications.*, posts.content, users.username 
            FROM notifications 
            JOIN posts ON notifications.post_id = posts.id 
            JOIN users ON posts.user_id = users.id 
            WHERE notifications.user_id = ? 
            ORDER BY notifications.created_at DESC");
$stmt->execute([$_SESSION["user_id"]]);
$notifications = $stmt->fetchAll();
?>

<h1>Notifications</h1>
<div class="notifications-list">
    <?php foreach ($notifications as $notification): ?>
        <div class="notification">
            <!-- Afficher le message de notification -->
            <p><?= htmlspecialchars($notification["message"]) ?></p>
            <small><?= date("d M Y H:i", strtotime($notification["created_at"])) ?></small>
            <a href="newsfeed.php#post-<?= $notification['post_id'] ?>">Voir la publication</a>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>