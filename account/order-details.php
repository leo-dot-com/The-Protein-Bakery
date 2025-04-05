<?php
require_once '../includes/header.php';
require_once '../includes/db_connection.php';
require_once '../includes/auth_functions.php';
require_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$orderId = $_GET['id'];

// Verify order belongs to user
$stmt = $pdo->prepare("
    SELECT o.* 
    FROM orders o
    WHERE o.id = ? AND (o.user_id = ? OR o.email = ?)
");
$stmt->execute([$orderId, $_SESSION['user_id'], $_SESSION['user_email']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_path
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="row">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Order #<?php echo $order['id']; ?></h2>
                <div>
                    <span class="badge bg-<?php 
                        echo $order['status'] === 'Delivered' ? 'success' : 
                             ($order['status'] === 'processing' ? 'warning' : 'secondary'); 
                    ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                    <span class="ms-2 text-muted"><?php echo date('F j, Y \a\t g:i a', strtotime($order['created_at'])); ?></span>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="/protein-bakery/assets/images/<?php echo htmlspecialchars($item['image_path']); ?>" 
                                                         class="me-3" width="60" height="60" style="object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                        <small class="text-muted">SKU: <?php echo $item['product_id']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>HKD <?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>HKD <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Delivery Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Shipping Address</h6>
                                    <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Contact Information</h6>
                                    <p>
                                        <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                        <?php echo htmlspecialchars($order['email']); ?><br>
                                        <?php echo htmlspecialchars($order['phone']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>HKD <?php echo number_format($order['subtotal'], 2); ?></span>
                            </div>
                            <?php if ($order['discount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-danger">
                                <span>Discount:</span>
                                <span>-HKD <?php echo number_format($order['discount'], 2); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>HKD 0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Total:</span>
                                <span>HKD <?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Actions</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($order['status'] === 'processing'): ?>
                                <button class="btn btn-outline-danger btn-sm w-100 mb-2">
                                    <i class="fas fa-times me-2"></i>Cancel Order
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-outline-primary btn-sm w-100 mb-2">
                                <i class="fas fa-print me-2"></i>Print Invoice
                            </button>
                            <button class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-question-circle me-2"></i>Get Help
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>