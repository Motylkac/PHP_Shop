<?php
include '../link.php';

if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: home.php");
    exit;
}

$message = '';
$message_type = '';

// Handle star rating rendering for admin preview
function renderStars($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    $html = '<div class="star-rating">';
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<span class="star full">&#9733;</span>';
    }
    if ($halfStar) {
        $html .= '<span class="star half">&#9733;</span>';
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<span class="star empty">&#9733;</span>';
    }
    $html .= '</div>';
    return $html;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_item'])) {
        $item_id = intval($_POST['item_id']);
        $item_name = $conn->real_escape_string($_POST['item_name']);
        $item_price = floatval($_POST['item_price']);
        $item_quantity = intval($_POST['item_quantity']);
        $item_rating = floatval($_POST['item_rating']);
        $item_description = $conn->real_escape_string($_POST['item_description']);

        // Handle image upload if file provided
        $image_sql = "";
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $img_data = file_get_contents($_FILES['item_image']['tmp_name']);
            $img_data_escaped = $conn->real_escape_string($img_data);
            $image_sql = ", Item_Image = '$img_data_escaped'";
        }

        $update_sql = "UPDATE articles SET 
            Item_Name = '$item_name', 
            Item_Price = $item_price, 
            Item_Quantity = $item_quantity,
            Item_Rating = $item_rating,
            Item_Description = '$item_description'
            $image_sql
            WHERE Item_ID = $item_id";

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
        $item_price = floatval($_POST['new_item_price']);
        $item_quantity = intval($_POST['new_item_quantity']);
        $item_rating = floatval($_POST['new_item_rating'] ?? 0);
        $item_description = $conn->real_escape_string($_POST['new_item_description']);

        $img_data_escaped = "NULL";
        if (isset($_FILES['new_item_image']) && $_FILES['new_item_image']['error'] === UPLOAD_ERR_OK) {
            $img_data = file_get_contents($_FILES['new_item_image']['tmp_name']);
            $img_data_escaped = "'" . $conn->real_escape_string($img_data) . "'";
        }

        $insert_sql = "INSERT INTO articles (Item_Name, Item_Price, Item_Quantity, Item_Rating, Item_Description, Item_Image) 
                       VALUES ('$item_name', $item_price, $item_quantity, $item_rating, '$item_description', $img_data_escaped)";
        if ($conn->query($insert_sql)) {
            $message = "Item added successfully!";
            $message_type = "success";
        } else {
            $message = "Error adding item: " . $conn->error;
            $message_type = "error";
        }
    }
}

// Fetch all items for listing
$items_sql = "SELECT * FROM articles";
$items_result = $conn->query($items_sql);

// Fetch item to edit if requested
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
    <style>
        /* Star rating styles, similar to home.php */
        .star-rating {
            font-size: 20px;
            line-height: 1;
            margin-bottom: 8px;
        }
        .star {
            display: inline-block;
            position: relative;
            color: #ccc; 
        }
        .star.full {
            color: #f5c518; 
        }
        .star.half {
            color: #ccc;
        }
        .star.half::before {
            content: '\2605'; 
            position: absolute;
            left: 0;
            top: 0;
            width: 50%;
            overflow: hidden;
            color: #f5c518; 
        }
        img.item-image-preview {
            max-height: 80px;
            max-width: 80px;
            border-radius: 5px;
            object-fit: contain;
            margin-bottom: 8px;
            border: 1px solid #ccc;
        }
        .form-group input[type="file"] {
            padding: 5px 0;
        }
        textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ccc;
            resize: vertical;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="admin-container">
    <h2>Manage Stock</h2>

    <?php if ($message): ?>
        <div class="message <?= htmlspecialchars($message_type) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($edit_item): ?>
    <div class="form-section">
        <h3>Edit Item: <?= htmlspecialchars($edit_item['Item_Name']) ?></h3>
        <form method="post" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="item_id" value="<?= $edit_item['Item_ID'] ?>">
            <div class="form-group">
                <label>Item Name:</label>
                <input type="text" name="item_name" value="<?= htmlspecialchars($edit_item['Item_Name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Price (PLN):</label>
                <input type="number" step="0.01" name="item_price" value="<?= $edit_item['Item_Price'] ?>" min="0" required>
            </div>
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="item_quantity" value="<?= $edit_item['Item_Quantity'] ?>" min="0" required>
            </div>
            <div class="form-group">
                <label>Rating (0 to 5):</label>
                <input type="number" step="0.1" min="0" max="5" name="item_rating" value="<?= $edit_item['Item_Rating'] ?? 0 ?>" required>
                <div>
                    Preview: <?= renderStars(floatval($edit_item['Item_Rating'] ?? 0)) ?>
                </div>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="item_description" rows="4" required><?= htmlspecialchars($edit_item['Item_Description']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Current Image:</label><br>
                <?php if (!empty($edit_item['Item_Image'])): ?>
                    <?php $imgData = base64_encode($edit_item['Item_Image']); ?>
                    <img class="item-image-preview" src="data:image/png;base64,<?= $imgData ?>" alt="Current Image">
                <?php else: ?>
                    <p>No image available</p>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Upload New Image (optional):</label>
                <input type="file" name="item_image" accept="image/*">
            </div>
            <button type="submit" name="update_item" class="btn btn-primary">Update Item</button>
            <a href="manage_stock.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php endif; ?>

    <div class="form-section">
        <h3>Add New Item</h3>
        <form method="post" enctype="multipart/form-data" class="admin-form">
            <div class="form-group">
                <label>Item Name:</label>
                <input type="text" name="new_item_name" required>
            </div>
            <div class="form-group">
                <label>Price (PLN):</label>
                <input type="number" step="0.01" name="new_item_price" min="0" required>
            </div>
            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="new_item_quantity" min="0" required>
            </div>
            <div class="form-group">
                <label>Rating (0 to 5):</label>
                <input type="number" step="0.1" min="0" max="5" name="new_item_rating" value="0" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="new_item_description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Upload Image (optional):</label>
                <input type="file" name="new_item_image" accept="image/*">
            </div>
            <button type="submit" name="add_item" class="btn btn-success">Add Item</button>
        </form>
    </div>

    <hr>

    <h3>All Items</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Description</th>
                <th>Rating</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items_result && $items_result->num_rows > 0): ?>
                <?php while ($item = $items_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $item['Item_ID'] ?></td>
                        <td>
                            <?php if (!empty($item['Item_Image'])): ?>
                                <?php $imgData = base64_encode($item['Item_Image']); ?>
                                <img class="item-image-preview" src="data:image/png;base64,<?= $imgData ?>" alt="Item Image">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['Item_Name']) ?></td>
                        <td><?= number_format($item['Item_Price'], 2) ?> PLN</td>
                        <td><?= $item['Item_Quantity'] ?></td>
                        <td>
                            <?php
                            $descPreview = strip_tags($item['Item_Description']);
                            if (strlen($descPreview) > 100) {
                                $descPreview = substr($descPreview, 0, 100) . '...';
                            }
                            echo htmlspecialchars($descPreview);
                            ?>
                        </td>
                        <td><?= renderStars(floatval($item['Item_Rating'] ?? 0)) ?></td>
                        <td>
                            <a href="manage_stock.php?edit=<?= $item['Item_ID'] ?>" class="btn btn-edit">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">No items found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
