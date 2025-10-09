<?php
include '../link.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['quantity'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    
    $username = $_SESSION['username'];
    $user_sql = "SELECT ID FROM Users WHERE Username = '$username' LIMIT 1";
    $user_result = $conn->query($user_sql);
    
    if ($user_result && $user_result->num_rows === 1) {
        $user = $user_result->fetch_assoc();
        $user_id = $user['ID'];
        
        $check_sql = "SELECT * FROM cart WHERE user_id = $user_id AND item_id = $item_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $update_sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND item_id = $item_id";
            $conn->query($update_sql);
        } else {
            $insert_sql = "INSERT INTO cart (user_id, item_id, quantity) VALUES ($user_id, $item_id, $quantity)";
            $conn->query($insert_sql);
        }
        
        header("Location: cart.php");
        exit;
    }
}

header("Location: home.php");
exit;
?>