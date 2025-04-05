<?php
session_start();
$pageTitle = "Your Cart";
require_once 'includes/header.php';
require_once 'includes/db_connection.php';

// Add to cart logic
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add or update item in cart
    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }
    
    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit;
}

// Remove from cart logic
if (isset($_GET['remove'])) {
    $productId = $_GET['remove'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header("Location: cart.php");
    exit;
}

// Update quantity logic
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $productId => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$productId] = $quantity;
        } else {
            unset($_SESSION['cart'][$productId]);
        }
    }
    header("Location: cart.php");
    exit;
}
?>

<div class="container my-5">
    <h1 class="mb-4">Your Shopping Cart</h1>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="products.php">Browse our products</a>
        </div>
    <?php else: ?>
        <form action="cart.php" method="post">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $productId => $quantity):
                        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                        $stmt->execute([$productId]);
                        $product = $stmt->fetch();
                        $subtotal = $product['price'] * $quantity;
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td>
                            <img src="assets/images/<?php echo htmlspecialchars($product['image_path']); ?>" width="50" class="me-2">
                            <a href="/the-protein-bakery/product.php?id=<?php echo $productId; ?>" class="cart-link"><?php echo htmlspecialchars($product['name']); ?></a>
                        </td>
                        <td>HKD <?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <input type="number" name="quantities[<?php echo $productId; ?>]" value="<?php echo $quantity; ?>" min="1" class="form-control" style="width: 70px;">
                        </td>
                        <td>HKD <?php echo number_format($subtotal, 2); ?></td>
                        <td>
                            <a href="cart.php?remove=<?php echo $productId; ?>" class="btn btn-sm btn-danger">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="2"><strong>HKD <?php echo number_format($total, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="d-flex justify-content-between">
                <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
                <div>
                    <button type="submit" name="update_cart" class="btn btn-outline-primary me-2">Update Cart</button>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>