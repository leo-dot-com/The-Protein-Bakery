<?php
$pageTitle = "Products";
require_once 'includes/header.php';
require_once 'includes/db_connection.php';

// Available filters (match these with your product names/descriptions)
$ingredientFilters = [
    'pistachios' => 'Pistachios',
    'banana' => 'Banana',
    'walnuts' => 'Walnuts',
    'sea-salt' => 'Sea Salt'
];

// Get selected filters from URL
$selectedFilters = [];
foreach ($ingredientFilters as $key => $value) {
    if (isset($_GET[$key])) {
        $selectedFilters[] = $key;
    }
}
?>

<div class="container">
    <h1 class="my-5">Our Protein Bars</h1>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filters</h5>
                </div>
                <div class="card-body">
                    <form id="filter-form" method="get">
                        <h6>Ingredients</h6>
                        <?php foreach ($ingredientFilters as $key => $label): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input filter-checkbox" 
                                       type="checkbox" 
                                       name="<?php echo $key; ?>" 
                                       id="<?php echo $key; ?>"
                                       <?php echo in_array($key, $selectedFilters) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="<?php echo $key; ?>">
                                    <?php echo htmlspecialchars($label); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-sm btn-primary mt-3">Apply Filters</button>
                        <?php if (!empty($selectedFilters)): ?>
                            <a href="products.php" class="btn btn-sm btn-outline-secondary mt-3">Clear All</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="row" id="product-container">
                <?php
                // Build SQL query based on filters
                $query = "SELECT * FROM products";
                $params = [];
                
                if (!empty($selectedFilters)) {
                    $conditions = [];
                    foreach ($selectedFilters as $filter) {
                        $conditions[] = "name LIKE ?";
                        $params[] = "%$filter%";
                    }
                    $query .= " WHERE " . implode(" OR ", $conditions);
                }
                
                $query .= " ORDER BY name";
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                
                if ($stmt->rowCount() > 0) {
                    while ($product = $stmt->fetch()):
                ?>
                <div class="col-lg-4 col-md-6 mb-4 product-card">
                <div class="card h-100">
                        <img src="assets/images/<?php echo htmlspecialchars($product['image_path']); ?>" 
                            class="card-img-top" 
                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text">HKD <?php echo number_format($product['price'], 2); ?></p>
                            
                            <!-- Add inventory display -->
                            <div class="inventory mb-2">
                                <?php if ($product['inventory'] > 0): ?>
                                    <span class="badge bg-success">In Stock: <?php echo $product['inventory']; ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Temporarily Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <form action="cart.php" method="post">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" 
                                    class="btn btn-sm btn-outline-secondary">Details</a>
                                    
                                    <?php if ($product['inventory'] > 0): ?>
                                        <button type="submit" name="add_to_cart" class="btn btn-sm btn-primary">
                                            Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            Not Available
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">No products match your filters.</div></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript for better UX (optional)
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.filter-checkbox');
    
    // Auto-submit form when checkbox is clicked (optional)
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>