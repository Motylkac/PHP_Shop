<?php
include '../link.php';

// Registration logic - moved to register.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'], $_POST['confirm_password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $check_sql = "SELECT ID FROM Users WHERE Username = '$username' LIMIT 1";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $error = "Username already exists.";
        } else {
            // Insert new user (regular user, not admin)
            $passwordHash = hash('sha512', $password);
            $insert_sql = "INSERT INTO Users (Username, PasswordHash, isAdmin) VALUES ('$username', '$passwordHash', 0)";
            
            if ($conn->query($insert_sql)) {
                // Redirect to login page after successful registration
                header("Location: login.php?success=Registration successful! You can now log in.");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Register</title>
    <link rel="stylesheet" href="../styles/register_s.css">     
</head>
<body>
    <div class="register-box">
        <h2>Create Account</h2>
        <?php if (!empty($error)): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="register.php">
            <label>Username:
                <input type="text" name="username" required />
            </label>
            <label>Password:
                <input type="password" name="password" required />
            </label>
            <label>Confirm Password:
                <input type="password" name="confirm_password" required />
            </label>
            <button type="submit">Register</button>
        </form>
        <a class="login-link" href="login.php">Already have an account? Log in</a>
        <a class="back-link" href="../site/home.php">Back to main page</a>
    </div>
</body>
</html>