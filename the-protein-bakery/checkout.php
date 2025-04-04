<?php
require_once 'includes/header.php';
require_once 'includes/db_connection.php';
require_once 'includes/auth_functions.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Apply loyalty discount if logged in
$discount = 0;
$discountAmount = 0;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT tier FROM user_loyalty WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $tier = $stmt->fetchColumn();
    
    if ($tier === 'bronze') $discount = 5;
    elseif ($tier === 'silver') $discount = 10;
    elseif ($tier === 'gold') $discount = 15;
}

// Calculate total with discount
$subtotal = 0;
$cartItems = [];
foreach ($_SESSION['cart'] as $productId => $quantity) {
    $stmt = $pdo->prepare("SELECT name, price, inventory FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    $subtotal += $product['price'] * $quantity;
    $cartItems[$productId] = $product;
}

$discountAmount = $subtotal * ($discount / 100);
$total = $subtotal - $discountAmount;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate inputs
$requiredFields = ['first_name', 'last_name', 'email', 'phone', 'address'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst($field) . " is required";
        }
    }
    
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check inventory
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        if ($cartItems[$productId]['inventory'] < $quantity) {
            $errors[] = "Only {$cartItems[$productId]['inventory']} {$cartItems[$productId]['name']} bars available";
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Deduct inventory
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $stmt = $pdo->prepare("UPDATE products SET inventory = inventory - ? WHERE id = ?");
                $stmt->execute([$quantity, $productId]);
            }
            
            // 2. Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders 
                (customer_name, email, phone, address, subtotal, discount, total_amount, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $userId = is_logged_in() ? $_SESSION['user_id'] : null;
            $stmt->execute([
                trim($_POST['first_name'] . ' ' . $_POST['last_name']),
                $_POST['email'],
                $_POST['phone'],
                $_POST['address'],
                $subtotal,
                $discountAmount,
                $total,
                $userId
            ]);
            $orderId = $pdo->lastInsertId();
            
            // 3. Create order items
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $stmt = $pdo->prepare("
                    INSERT INTO order_items 
                    (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $productId,
                    $quantity,
                    $cartItems[$productId]['price']
                ]);
            }
            
            // 4. Update loyalty points if logged in
            if (is_logged_in()) {
                $pointsEarned = floor($total / 10);
                $stmt = $pdo->prepare("
                    UPDATE user_loyalty 
                    SET points = points + ?, 
                        last_order_date = NOW() 
                    WHERE user_id = ?
                ");
                $stmt->execute([$pointsEarned, $_SESSION['user_id']]);
                
                // Check for tier upgrades
                $stmt = $pdo->prepare("
                    SELECT points FROM user_loyalty WHERE user_id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $points = $stmt->fetchColumn();
                
                $newTier = 'regular';
                if ($points >= $goldThreshold) {
                    $newTier = 'gold';
                } elseif ($points >= $silverThreshold) {
                    $newTier = 'silver';
                } elseif ($points >= $bronzeThreshold) {
                    $newTier = 'bronze';
                }
                
                $stmt = $pdo->prepare("
                    UPDATE user_loyalty SET tier = ? WHERE user_id = ?
                ");
                $stmt->execute([$newTier, $_SESSION['user_id']]);
            }
            
            // Send confirmation email
            require_once 'includes/email_functions.php';
            $items = [];
            foreach ($_SESSION['cart'] as $productId => $quantity) {
                $items[] = [
                    'name' => $cartItems[$productId]['name'],
                    'quantity' => $quantity,
                    'price' => $cartItems[$productId]['price']
                ];
            }
            
            sendOrderEmails([
                'order_id' => $orderId,
                'customer_name' => trim($_POST['first_name'] . ' ' . $_POST['last_name']),
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address'],
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'total' => $total,
                'items' => $items
            ]);

            $pdo->commit();
            unset($_SESSION['cart']);
            header("Location: order-confirmation.php?id=" . $orderId);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Order processing failed: " . $e->getMessage();
        }
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-4">Checkout</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" action="checkout.php">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo is_logged_in() ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) : ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo is_logged_in() ? htmlspecialchars(explode(' ', $_SESSION['user_name'])[1]) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo is_logged_in() ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Delivery Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                
                <h4 class="mb-3 mt-5">Order Summary</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $productId => $quantity): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cartItems[$productId]['name']); ?></td>
                            <td><?php echo $quantity; ?></td>
                            <td>HKD <?php echo number_format($cartItems[$productId]['price'] * $quantity, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2" class="text-end"><strong>Subtotal:</strong></td>
                            <td><strong>HKD <?php echo number_format($subtotal, 2); ?></strong></td>
                        </tr>
                        <?php if ($discount > 0): ?>
                        <tr>
                            <td colspan="2" class="text-end"><strong>Discount (<?php echo $discount; ?>%):</strong></td>
                            <td><strong class="text-danger">-HKD <?php echo number_format($discountAmount, 2); ?></strong></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="2" class="text-end"><strong>Total:</strong></td>
                            <td><strong>HKD <?php echo number_format($total, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>