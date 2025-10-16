<?php
include '../link.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../log/login.php");
    exit;
}

$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;

$username = $_SESSION['username'];
$user_sql = "SELECT ID FROM Users WHERE Username = '$username' LIMIT 1";
$user_result = $conn->query($user_sql);
$user_id = null;

if ($user_result && $user_result->num_rows === 1) {
    $user = $user_result->fetch_assoc();
    $user_id = $user['ID'];
}

$cart_items = [];
$total_price = 0;

if ($user_id) {
    $cart_sql = "SELECT c.*, a.Item_Name, a.Item_Price, a.Item_Image 
                 FROM cart c 
                 JOIN articles a ON c.item_id = a.Item_ID 
                 WHERE c.user_id = $user_id";
    $cart_result = $conn->query($cart_sql);

    if ($cart_result && $cart_result->num_rows > 0) {
        while ($item = $cart_result->fetch_assoc()) {
            $cart_items[] = $item;
            $total_price += $item['Item_Price'] * $item['quantity'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cart_id = intval($_POST['remove_item']);
    $remove_sql = "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";
    $conn->query($remove_sql);
    header("Location: cart.php?msg=removed");
    exit;
}

// Handle success messages via query parameter
$message = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'removed':
            $message = "Item removed from cart!";
            break;
        case 'cleared':
            $message = "Cart has been cleared!";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Cart</title>
    <link rel="stylesheet" href="../styles/navbar_s.css?v=1.1">
    <link rel="stylesheet" href="../styles/cart_s.css?v=1.2">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="cart-container">
    <h2>Your Cart</h2>

    <?php if (!empty($message)): ?>
        <div class="success-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="clear_cart.php" method="post" class="clear-form">
        <button type="submit" class="clear-btn">Clear Cart</button>
    </form>

    <?php if ($isAdmin): ?>
        <div class="admin-notice">
            <p>You are viewing cart as Administrator</p>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <p class="empty-cart">Your cart is empty</p>
    <?php else: ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <?php if (!empty($item['Item_Image'])): ?>
                        <?php $imgData = base64_encode($item['Item_Image']); ?>
                        <img src="data:image/png;base64,<?= $imgData ?>" alt="<?= htmlspecialchars($item['Item_Name']) ?>">
                    <?php endif; ?>

                    <div class="item-details">
                        <h3><?= htmlspecialchars($item['Item_Name']) ?></h3>
                        <p class="item-price">Price: <?= number_format($item['Item_Price'], 2, '.', ' ') ?> PLN</p>
                        <p class="item-quantity">Quantity: <?= $item['quantity'] ?></p>
                        <p class="item-total">Total: <?= number_format($item['Item_Price'] * $item['quantity'], 2, '.', ' ') ?> PLN</p>
                    </div>

                    <form method="post" class="remove-form">
                        <input type="hidden" name="remove_item" value="<?= $item['cart_id'] ?>">
                        <button type="submit" class="remove-btn">Remove</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <h3>Total: <?= number_format($total_price, 2, '.', ' ') ?> PLN</h3>
            <a href="payment.php" class="checkout-btn">Proceed to Payment</a>
        </div>
    <?php endif; ?>

    <a href="home.php" class="back-link">Back to shopping</a>
</div>

<script>
    setTimeout(() => {
        const msgBox = document.querySelector('.success-message');
        if (msgBox) msgBox.style.display = 'none';
    }, 3000);
</script>

</body>
</html>
