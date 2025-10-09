<?php
$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;
?>

<div class="top-right <?= $isAdmin ? 'admin' : 'user' ?>">
    <div><a href="home.php">Home</a></div>
    <div><a href="cart.php">Cart</a></div>
    <div><a href="payment.php">Pay</a></div>
    <?php if ($isAdmin): ?>
        <div><a href="manage_stock.php">Manage Stock</a></div>
        <div><a href="manage_accounts.php">Manage Accounts</a></div>
    <?php endif; ?>
    <div>
        <?php if (isset($_SESSION['username'])): ?>
            <a href="../log/logout.php">Logout</a>
        <?php else: ?>
            <a href="../log/login.php">Log in</a>
        <?php endif; ?>
    </div>
</div>