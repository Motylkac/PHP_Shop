<?php
include '../link.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../log/login.php");
    exit;
}

$username = $_SESSION['username'];
$user_sql = "SELECT ID FROM Users WHERE Username = '$username' LIMIT 1";
$user_result = $conn->query($user_sql);

if ($user_result && $user_result->num_rows === 1) {
    $user = $user_result->fetch_assoc();
    $user_id = $user['ID'];

    $clear_sql = "DELETE FROM cart WHERE user_id = $user_id";
    $conn->query($clear_sql);
}

header("Location: cart.php?msg=cleared");
exit;
?>
