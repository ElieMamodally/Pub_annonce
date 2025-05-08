<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
include 'includes/nav.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
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
    <!-- Lien vers mur.php avec l'ID de l'utilisateur -->
    <h1><a href="mur.php?id=<?= htmlspecialchars($user["id"]) ?>"><?= htmlspecialchars($user["username"]) ?></a></h1>
    
    <form class="profile-form" method="POST" enctype="multipart/form-data">
        <label>Modifier le nom :</label>
        <input type="text" name="username" placeholder="<?= htmlspecialchars($user["username"]) ?>">
        
        
        <!-- Photo de profile-->
        <label for="pdp">
            <p>Modifier la photo de profile :</p>
            <img src="./media/icônes/photo.png" alt="photo de couverture" style="width: 30px;">
            <input type="file" name="pdp" id="pdp" hidden>
            <p id="nameImgPdpUploaded">Aucun image séléctionné</p>
            <img id="pdpselected" src="" alt="">
        </label>


        <!-- Photo de couverture-->
        <label for="pdc">
            <p>Modifier la photo de couverture :</p>
            <img src="./media/icônes/photo.png" alt="photo de couverture" style="width: 30px;">
            <input type="file" name="pdc" id="pdc" hidden>
            <p id="nameImg-PdcUploaded">Aucun image séléctionné</p>
            <img id="pdcselected" src="" alt="">

        </label>
        <button type="submit">Enregistrer</button>
    </form>
</div>

<?php
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>
<?php include 'includes/footer.php'; ?>
<style>
    label{
        display: block;
        justify-content: space-between;
        align-items: center;
    }
    #nameImg-PdcUploaded,#nameImgPdpUploaded{
        color: grey;
    }
</style>