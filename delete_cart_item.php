<?php
session_start();
include("config.php");

if (!isset($_SESSION["uid"])) {
    header("location:login.php");
    exit;
}

if (isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];
    $uid = $_SESSION['uid'];

    // Delete only if the cart item belongs to the logged-in user
    $stmt = $conn->prepare("DELETE FROM cart WHERE id=? AND uid=?");
    $stmt->bind_param("ii", $cart_id, $uid);
    $stmt->execute();
    $stmt->close();
}

header("location: cart.php");
exit;
?>
