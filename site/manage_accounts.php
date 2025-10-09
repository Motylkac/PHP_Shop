<?php
include '../link.php';

// Check if user is admin
if (!isset($_SESSION['isAdmin']) || !$_SESSION['isAdmin']) {
    header("Location: home.php");
    exit;
}

$message = '';
$message_type = '';

// Handle account actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_admin'])) {
        $user_id = intval($_POST['user_id']);
        $current_admin = intval($_POST['current_admin']);
        $new_admin = $current_admin ? 0 : 1;
        
        $update_sql = "UPDATE Users SET isAdmin = $new_admin WHERE ID = $user_id";
        if ($conn->query($update_sql)) {
            $message = "User admin status updated!";
            $message_type = "success";
        } else {
            $message = "Error updating user: " . $conn->error;
            $message_type = "error";
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        $username = $conn->real_escape_string($_POST['username']);
        
        // Don't allow deleting yourself
        if ($user_id == $_SESSION['user_id'] ?? 0) {
            $message = "You cannot delete your own account!";
            $message_type = "error";
        } else {
            // First delete user's cart items
            $delete_cart_sql = "DELETE FROM cart WHERE user_id = $user_id";
            $conn->query($delete_cart_sql);
            
            // Then delete the user
            $delete_user_sql = "DELETE FROM Users WHERE ID = $user_id";
            if ($conn->query($delete_user_sql)) {
                $message = "User '$username' deleted successfully!";
                $message_type = "success";
            } else {
                $message = "Error deleting user: " . $conn->error;
                $message_type = "error";
            }
        }
    }
}

// Fetch all users
$users_sql = "SELECT ID, Username, isAdmin FROM Users";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Accounts</title>
    <link rel="stylesheet" href="../styles/navbar_s.css?v=1.1">
    <link rel="stylesheet" href="../styles/admin_s.css?v=1.1">
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="admin-container">
    <h2>Manage Accounts</h2>
    
    <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="users-list">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users_result && $users_result->num_rows > 0): ?>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['ID'] ?></td>
                            <td><?= htmlspecialchars($user['Username']) ?></td>
                            <td>
                                <?php if ($user['isAdmin']): ?>
                                    <span class="role-admin">Administrator</span>
                                <?php else: ?>
                                    <span class="role-user">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['ID'] ?>">
                                    <input type="hidden" name="current_admin" value="<?= $user['isAdmin'] ?>">
                                    <button type="submit" name="toggle_admin" class="btn btn-<?= $user['isAdmin'] ? 'warning' : 'primary' ?>">
                                        <?= $user['isAdmin'] ? 'Remove Admin' : 'Make Admin' ?>
                                    </button>
                                </form>
                                
                                <?php if ($user['ID'] != ($_SESSION['user_id'] ?? 0)): ?>
                                <form method="post" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete user <?= htmlspecialchars($user['Username']) ?>? This action cannot be undone.')">
                                    <input type="hidden" name="user_id" value="<?= $user['ID'] ?>">
                                    <input type="hidden" name="username" value="<?= htmlspecialchars($user['Username']) ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>