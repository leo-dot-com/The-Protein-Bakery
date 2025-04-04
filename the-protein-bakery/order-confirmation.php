<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Order Confirmation";
require_once 'includes/header.php';
require_once 'includes/db_connection.php';

$orderId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();
?>

<div class="container my-5">
    <div class="text-center mb-5">
        <i class="fas fa-check-circle text-success fa-5x mb-3"></i>
        <h1>Thank You for Your Order!</h1>
        <p class="lead">Your order #<?php echo $order['id']; ?> has been received.</p>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p>
                                <strong>Order #:</strong> <?php echo $order['id']; ?><br>
                                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?><br>
                                <strong>Total:</strong> HKD <?php echo number_format($order['total_amount'], 2); ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p>
                                <strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                <strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?><br>
                                <strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?>
                            </p>
                        </div>
                    </div>
                    
                    <h6>Delivery Address</h6>
                    <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                    
                    <h6 class="mt-4">Order Items</h6>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>HKD <?php echo number_format($item['price'], 2); ?></td>
                                <td>HKD <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>