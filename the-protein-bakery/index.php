<?php 
$pageTitle = "Home";
require_once 'includes/header.php'; 
?>

<section class="hero-section bg-light py-5">
    <div class="container text-center">
        <h1 class="display-4">Homemade Protein Bars</h1>
        <p class="lead">Crafted with premium ingredients for fitness enthusiasts</p>
        <a href="products.php" class="btn btn-primary btn-lg">Shop Now</a>
    </div>
</section>

<section class="featured-products py-5">
    <div class="container">
        <h2 class="text-center mb-5">Our Bestsellers</h2>
        <div class="row">
            <?php
            require_once 'includes/db_connection.php';
            $stmt = $pdo->query("SELECT * FROM products LIMIT 3");
            while ($product = $stmt->fetch()):
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="assets/images/<?php echo htmlspecialchars($product['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text">HKD <?php echo number_format($product['price'], 2); ?></p>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">View Details</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>