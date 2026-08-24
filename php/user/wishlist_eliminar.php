<?php
session_start();
if ($_SESSION['rol'] != 'user') { header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

$id = intval($_GET['id']);
$conn->query("DELETE FROM wishlist WHERE id=$id");

header("Location: wishlist.php");
exit;
?>
