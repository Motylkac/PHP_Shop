<?php
include '../link.php';

$isAdmin = isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true;
$isLoggedIn = isset($_SESSION['username']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'], $_POST['quantity']) && $isLoggedIn) {
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
            if ($conn->query($update_sql)) {
                $success_message = "Item quantity updated in cart!";
            }
        } else {
            $insert_sql = "INSERT INTO cart (user_id, item_id, quantity) VALUES ($user_id, $item_id, $quantity)";
            if ($conn->query($insert_sql)) {
                $success_message = "Item added to cart!";
            }
        }
    }
}

$sql = "SELECT Item_ID, Item_Name, Item_Image, Item_Price, Item_Quantity FROM articles";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Strona główna</title>
    <link rel="stylesheet" href="../styles/navbar_s.css?v=1.1">
    <link rel="stylesheet" href="../styles/home_s.css?v=1.1">
</head>
<body>

<?php include 'navbar.php'; ?>

<h1>
    <?php 
        if (isset($_SESSION['username'])) {
            echo "Witaj " . htmlspecialchars($_SESSION['username']);
            if ($isAdmin) {
                echo " (Admin)";
            }
            echo "!";
        } else {
            echo "Witaj!";
        }
    ?>
</h1>

<h2>Dostępne artykuły BHP:</h2>

<?php if (isset($success_message)): ?>
    <div class="success-message">
        <?= htmlspecialchars($success_message) ?>
        <a href="cart.php" class="view-cart-link">View Cart</a>
    </div>
<?php endif; ?>

<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="article<?= $isAdmin ? ' admin-article' : '' ?>">
            <h3><?= htmlspecialchars($row['Item_Name']) ?></h3>
            <?php
            if (!empty($row['Item_Image'])) {
                $imgData = base64_encode($row['Item_Image']);
                echo '<img src="data:image/png;base64,' . $imgData . '" alt="' . htmlspecialchars($row['Item_Name']) . '">';
            } else {
                echo '<p>Brak dostępnego zdjęcia</p>';
            }
            ?>
            <p><strong>Cena:</strong> <?= htmlspecialchars($row['Item_Price']) ?> PLN</p>
            <p><strong>Dostępna ilość:</strong> <?= htmlspecialchars($row['Item_Quantity']) ?></p>
            
            <?php if ($isLoggedIn): ?>
                <form method="post" action="home.php" class="add-to-cart-form">
                    <input type="hidden" name="item_id" value="<?= $row['Item_ID'] ?>">
                    <input type="hidden" name="item_name" value="<?= htmlspecialchars($row['Item_Name']) ?>">
                    <input type="hidden" name="item_price" value="<?= $row['Item_Price'] ?>">
                    <label for="quantity_<?= $row['Item_ID'] ?>">Ilość:</label>
                    <input type="number" name="quantity" id="quantity_<?= $row['Item_ID'] ?>" 
                           min="1" max="<?= $row['Item_Quantity'] ?>" value="1" required>
                    <button type="submit" class="add-to-cart-btn">Dodaj do koszyka</button>
                </form>
                
                <?php if ($isAdmin): ?>
                    <div class="admin-actions">
                        <a href="manage_stock.php?edit=<?= $row['Item_ID'] ?>" class="edit-btn">Edit Item</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="login-required">Zaloguj się, aby dodać do koszyka</p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Brak dostępnych artykułów.</p>
<?php endif; ?>

</body>
</html>