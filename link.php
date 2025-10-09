<?php
session_start();

$host = 'localhost';
$db = 'BHP';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';
$success = '';

if (basename($_SERVER['PHP_SELF']) === 'login.php' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $passwordHash = hash('sha512', $password);

    $sql = "SELECT ID, Username, isAdmin FROM Users WHERE Username = '$username' AND PasswordHash = '$passwordHash' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['Username'];
        $_SESSION['isAdmin'] = (bool)$user['isAdmin'];
        header("Location: ../site/home.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>