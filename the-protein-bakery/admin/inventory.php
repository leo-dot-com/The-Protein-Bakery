<?php
session_start();
require_once '../includes/db_connection.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Inventory Management";
require_once '../includes/header.php';

// Handle inventory updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    foreach ($_POST['inventory'] as $productId => $quantity) {
        $stmt = $pdo->prepare("UPDATE products SET inventory = ? WHERE id = ?");
        $stmt->execute([$quantity, $productId]);
    }
    $success = "Inventory updated successfully";
}

// Get all products
$stmt = $pdo->query("SELECT id, name, price, inventory FROM products ORDER BY name");
$products = $stmt->fetchAll();

// Check low inventory
$lowInventory = $pdo->query("SELECT COUNT(*) FROM products WHERE inventory < 5")->fetchColumn();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Management</h2>
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
    
    <?php if ($lowInventory > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <?php echo $lowInventory; ?> product(s) have low inventory (less than 5)
        </div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="post">
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Current Inventory</th>
                    <th>Update To</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>HKD <?php echo number_format($product['price'], 2); ?></td>
                    <td>
                        <span class="<?php echo $product['inventory'] < 5 ? 'text-danger fw-bold' : 'text-success'; ?>">
                            <?php echo $product['inventory']; ?>
                        </span>
                    </td>
                    <td>
                        <input type="number" name="inventory[<?php echo $product['id']; ?>]" 
                               value="<?php echo $product['inventory']; ?>" 
                               min="0" class="form-control" style="width: 100px;">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-end mt-3">
            <button type="submit" name="update_inventory" class="btn btn-primary">
                <i class="fas fa-save"></i> Save All Changes
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>