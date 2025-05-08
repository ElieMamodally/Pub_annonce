<?php
session_start();
header("cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Controle: post-check=0, pre-check=0, false");
header("Pragma: no-cache");
session_destroy();
header("Location: index.php");
exit();
?>
