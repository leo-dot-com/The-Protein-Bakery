<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$pageTitle = "Product Details";
require_once 'includes/header.php';
require_once 'includes/auth_functions.php';
require_once 'includes/db_connection.php';

$productId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

// Get reviews
$reviewsStmt = $pdo->prepare("
    SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name
    FROM product_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$reviewsStmt->execute([$productId]);
$reviews = $reviewsStmt->fetchAll();

// Calculate average rating
$ratingStmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM product_reviews WHERE product_id = ?");
$ratingStmt->execute([$productId]);
$ratingInfo = $ratingStmt->fetch();

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) {
    $rating = (float)$_POST['rating'];
    
    // Validate rating (0.5 to 5 in 0.5 increments)
    if ($rating < 0.5 || $rating > 5 || fmod($rating * 2, 1) != 0) {
        $reviewError = "Please select a valid rating";
    } else {
        // Save to database
        $stmt = $pdo->prepare("INSERT INTO product_reviews (...) VALUES (?, ?, ?, ?)");
        $stmt->execute([..., $rating, ...]);
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <img src="assets/images/<?php echo htmlspecialchars($product['image_path']); ?>" 
                 class="img-fluid rounded" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <!-- Rating display -->
            <div class="mb-3">
                <?php if ($ratingInfo['count'] > 0): ?>
                    <div class="star-rating">
                        <?php
                        $fullStars = floor($review['rating']);
                        $hasHalfStar = ($review['rating'] - $fullStars) >= 0.5;
                        
                        for ($i = 1; $i <= 5; $i++): 
                            if ($i <= $fullStars): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star text-secondary"></i>
                            <?php endif;
                        endfor; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted">No reviews yet</span>
                <?php endif; ?>
            </div>
            
            <h3 class="text-primary">HKD <?php echo number_format($product['price'], 2); ?></h3>
            <p class="lead"><?php echo htmlspecialchars($product['description']); ?></p>
            
            <h4>Ingredients</h4>
            <p><?php echo htmlspecialchars($product['ingredients']); ?></p>
            
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
    
    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3>Customer Reviews</h3>
            
            <?php if (is_logged_in()): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Write a Review</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($reviewError)): ?>
                            <div class="alert alert-danger"><?php echo $reviewError; ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="star-rating-input">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                        <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="star-rating">
                                    <?php
                                    $fullStars = floor($review['rating']);
                                    $hasHalfStar = (fmod($review['rating'], 1) >= 0.5);
                                    
                                    for ($i = 1; $i <= 5; $i++): 
                                        if ($i <= $fullStars): ?>
                                            <i class="fas fa-star text-warning"></i>
                                        <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-secondary"></i>
                                        <?php endif;
                                    endfor; ?>
                                    <span class="ms-2">(<?php echo number_format($review['rating'], 1); ?>)</span>
                                </div>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <a href="login.php" class="alert-link">Log in</a> to leave a review.
                </div>
            <?php endif; ?>
            
            <?php if (!empty($reviews)): ?>
                <div class="review-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title"><?php echo htmlspecialchars($review['user_name']); ?></h5>
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                <p class="card-text mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No reviews yet. Be the first to review!</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating .stars input');
    const ratingDisplay = document.getElementById('selected-rating');
    
    stars.forEach(star => {
        star.addEventListener('change', function() {
            ratingDisplay.textContent = this.value;
        });
        
        // For better UX, update display on hover
        star.addEventListener('mouseover', function() {
            ratingDisplay.textContent = this.value;
        });
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>
