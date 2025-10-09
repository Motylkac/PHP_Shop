<?php
include '../link.php';  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <link rel="stylesheet" href="../styles/login_s.css">     
</head>
<body>
    <div class="login-box">
        <h2>Log in</h2>
        <?php if (!empty($error)): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <p class="success-msg"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <label>Username:
                <input type="text" name="username" required />
            </label>
            <label>Password:
                <input type="password" name="password" required />
            </label>
            <button type="submit">Log In</button>
        </form>
        <a class="register-link" href="register.php">Don't have an account? Register</a>
        <a class="back-link" href="../site/home.php">Back to main page</a>
    </div>
</body>
</html>