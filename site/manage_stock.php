<?php
include '../link.php';

if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: home.php");
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_item'])) {
        $item_id = intval($_POST['item_id']);
        $item_name = $conn->real_escape_string($_POST['item_name']);
        $item_price = intval($_POST['item_price']);
        $item_quantity = intval($_POST['item_quantity']);
        
        $update_sql = "UPDATE articles SET Item_Name = '$item_name', Item_Price = $item_price, Item_Quantity = $item_quantity WHERE Item_ID = $item_id";
        if ($conn->query($update_sql)) {
            $message = "Item updated successfully!";
            $message_type = "success";
        } else {
            $message = "Error updating item: " . $conn->error;
            $message_type = "error";
        }
    }
    
    if (isset($_POST['add_item'])) {
        $item_name = $conn->real_escape_string($_POST['new_item_name']);
        $item_price = intval($_POST['new_item_price']);
        $item_quantity = intval($_POST['new_item_quantity']);
        
        $insert_sql = "INSERT INTO articles (Item_Name, Item_Price, Item_Quantity) VALUES ('$item_name', $item_price, $item_quantity)";
        if ($conn->query($insert_sql)) {
            $message = "Item added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding item: " . $conn->error;
            $message_type = "error";
        }
    }
}

$items_sql = "SELECT * FROM articles";
$items_result = $conn->query($items_sql);

$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_sql = "SELECT * FROM articles WHERE Item_ID = $edit_id";
    $edit_result = $conn->query($edit_sql);
    if ($edit_result && $edit_result->num_rows === 1) {
        $edit_item = $edit_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Stock</title>
    <link rel="stylesheet" href="../styles/navbar_s.css?v=1.1">
    <link rel="stylesheet" href="../styles/admin_s.css?v=1.1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="admin-container">
    <h2>Manage Stock</h2>
    
    <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($edit_item): ?>
    <div class="form-section">
        <h3>Edit Item: <?= htmlspecialchars($edit_item['Item_Name']) ?></h3>
        <form method="post" class="admin-form">
            <input type="hidden" name="item_id" value="<?= $edit_item['Item_ID'] ?>">
            <div class="form-group">
                <label>Item Name:</label>
                <input type="text" name="item_name" value="<?= htmlspecialchars($edit_item['Item_Name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Price (PLN):</label>
                <input type="number" name="item_price" value="<?= $edit_item['Item_Price'] ?>" min="0" required>
            </div>
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="item_quantity" value="<?= $edit_item['Item_Quantity'] ?>" min="0" required>
            </div>
            <button type="submit" name="update_item" class="btn btn-primary">Update Item</button>
            <a href="manage_stock.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="form-section">
        <h3>Add New Item</h3>
        <form method="post" class="admin-form">
            <div class="form-group">
                <label>Item Name:</label>
                <input type="text" name="new_item_name" required>
            </div>
            <div class="form-group">
                <label>Price (PLN):</label>
                <input type="number" name="new_item_price" min="0" required>
            </div>
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="new_item_quantity" min="0" required>
            </div>
            <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
        </form>
    </div>

    <div class="items-list">
        <h3>Current Items</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items_result && $items_result->num_rows > 0): ?>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $item['Item_ID'] ?></td>
                            <td><?= htmlspecialchars($item['Item_Name']) ?></td>
                            <td><?= $item['Item_Price'] ?> PLN</td>
                            <td><?= $item['Item_Quantity'] ?></td>
                            <td>
                                <a href="manage_stock.php?edit=<?= $item['Item_ID'] ?>" class="btn btn-edit">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>