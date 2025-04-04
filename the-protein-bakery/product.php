<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$pageTitle = "Product Details";
require_once 'includes/header.php';
require_once 'includes/db_connection.php';

$productId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

// Decode the nutritional info JSON
$nutritionalInfo = [];
if (!empty($product['nutritional_info'])) {
    $nutritionalInfo = json_decode($product['nutritional_info'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Fallback if JSON is invalid
        $nutritionalInfo = [];
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <img src="assets/images/<?php echo htmlspecialchars($product['image_path']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <h3 class="text-primary">HKD <?php echo number_format($product['price'], 2); ?></h3>
            <p class="lead"><?php echo htmlspecialchars($product['description']); ?></p>
            
            <h4>Ingredients</h4>
            <p><?php echo htmlspecialchars($product['ingredients']); ?></p>
            
            <h4>Nutritional Information</h4>
            <?php if (!empty($nutritionalInfo)): ?>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>Per 100g</th>
                            <th>Per Bar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nutritionalInfo as $category => $values): ?>
                            <?php if (is_array($values) && isset($values['per100g']) && isset($values['perBar'])): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $category))); ?></td>
                                    <td><?php echo htmlspecialchars($values['per100g']); ?></td>
                                    <td><?php echo htmlspecialchars($values['perBar']); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No nutritional information available.</p>
            <?php endif; ?>
            
            <form action="cart.php" method="post" class="mt-4">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity:</label>
                    <select class="form-select" id="quantity" name="quantity" style="width: 80px;">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">Add to Cart</button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>