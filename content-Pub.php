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
            $comments_stmt = $pdo->prepare("
            SELECT comments.*, users.username, users.pdp 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.post_id = ? 
            ORDER BY comments.created_at ASC
        ");
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
                    
            <!-- Menu Supprimer/Modifier -->
            <?php if ($_SESSION["user_id"] == $comment["user_id"]): ?>
                <div class="comment-menu">
                    <button class="menu-btn">⋮</button>
                    <div class="menu-content">
                        <a href="delete_comment.php?id=<?= $comment['id'] ?>" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</a>
                        <a href="edit_comment.php?id=<?= $comment['id'] ?>">Modifier</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Texte "Répondre" -->
            <span class="reply-text" onclick="toggleReplyForm(<?= $comment['id'] ?>)">Répondre</span>

            <!-- Formulaire de réponse (caché par défaut) -->
            <form id="reply-form-<?= $comment['id'] ?>" class="reply-form" action="add_comment.php" method="POST" style="display: none;">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                <input class="reply-input" type="text" name="comment" placeholder="Écrire une réponse..." required>
                <button class="reply-btn" type="submit">Envoyer</button>
            </form>

            <!-- Afficher les réponses -->
            <?php
            $replies_stmt = $pdo->prepare("
                SELECT comments.*, users.username, users.pdp 
                FROM comments 
                JOIN users ON comments.user_id = users.id 
                WHERE comments.parent_id = ? 
                ORDER BY comments.created_at ASC
            ");
            $replies_stmt->execute([$comment['id']]);
            $replies = $replies_stmt->fetchAll();

            foreach ($replies as $reply) {
                $reply_username = htmlspecialchars($reply["username"] ? $reply["username"] : "Utilisateur inconnu");
                $reply_pdp = htmlspecialchars($reply["pdp"] ? $reply["pdp"] : "default.png");
                ?>
                <div class="reply">
                    <strong>
                        <a href="mur.php?id=<?= htmlspecialchars($reply['user_id']) ?>">        
                            <img src="uploads/<?= $reply_pdp ?>" class="pdp">
                            <?= $reply_username ?>:</a>
                    </strong>
                    <p><?= htmlspecialchars($reply["content"]) ?></p>
                    <small><?= date("d M Y H:i", strtotime($reply["created_at"])) ?></small>

                    <!-- Menu Supprimer/Modifier pour les réponses -->
                    <?php if ($_SESSION["user_id"] == $reply["user_id"]): ?>
                        <div class="comment-menu">
                            <button class="menu-btn">⋮</button>
                            <div class="menu-content">
                                <a href="delete_comment.php?id=<?= $reply['id'] ?>" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</a>
                                <a href="edit_comment.php?id=<?= $reply['id'] ?>">Modifier</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
            ?>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="comment-form" action="add_comment.php" method="POST">
            <input type="hidden" name="post_id" value="<?= $post["id"] ?>">
            <input class="comment-text" type="text" name="comment" placeholder="Ajouter un commentaire..." required>
            <button class="comment-btn" type="submit">Envoyer</button>
        </form>
    </div>