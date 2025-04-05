<?php
require_once '../includes/header.php';
require_once '../includes/db_connection.php';
require_once '../includes/auth_functions.php';
require_login();

// Get user data
$stmt = $pdo->prepare("
    SELECT u.*, l.points, l.tier, t.discount
    FROM users u
    LEFT JOIN user_loyalty l ON u.id = l.user_id
    LEFT JOIN loyalty_tiers t ON l.tier = t.tier
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get recent orders
$orders = $pdo->prepare("
    SELECT o.id, o.created_at, o.total_amount, o.status, 
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$orders->execute([$_SESSION['user_id']]);
$recentOrders = $orders->fetchAll();

// Calculate loyalty discount
$discount = 0;
if ($user['tier'] === 'bronze') $discount = 5;
elseif ($user['tier'] === 'silver') $discount = 10;
elseif ($user['tier'] === 'gold') $discount = 15;
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>My Account</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-circle mb-2">
                            <span class="initials"><?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?></span>
                        </div>
                        <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Loyalty Tier
                            <span class="badge bg-<?php 
                                echo $user['tier'] === 'gold' ? 'warning' : 
                                     ($user['tier'] === 'silver' ? 'secondary' : 'bronze'); 
                            ?>">
                                <?php echo ucfirst($user['tier']); ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Points
                            <span><?php echo $user['points']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Current Discount
                            <span><?php echo $discount; ?>%</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Quick Links</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="orders.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-box-open me-2"></i> My Orders
                        </a>
                        <a href="addresses.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-map-marker-alt me-2"></i> Saved Addresses
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-cog me-2"></i> Account Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5>Recent Orders</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <div class="alert alert-info">You haven't placed any orders yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                        <td><?php echo $order['item_count']; ?></td>
                                        <td>HKD <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $order['status'] === 'completed' ? 'success' : 
                                                     ($order['status'] === 'processing' ? 'warning' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="/the-protein-bakeryorders.php" class="btn btn-primary">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
    <div class="card-header bg-primary text-white">
        <h5>Loyalty Program</h5>
    </div>
    <div class="card-body">
        <?php
        // Define tier thresholds
        $bronzeThreshold = 100;
        $silverThreshold = 500;   
        $goldThreshold = 1000;  
        
        // Then calculate progress based on points-to-gold ratio:
        $progress = ($user['points'] / $goldThreshold) * 100;
        
        // Calculate marker positions
        $bronzePos = ($bronzeThreshold / $goldThreshold) * 100;
        $silverPos = ($silverThreshold / $goldThreshold) * 100;
        ?>
        
        <div class="progress-container mb-3" style="position: relative; height: 30px;">
            <!-- Progress bar -->
            <div class="progress" style="height: 20px;">
                <div class="progress-bar bg-success" 
                     role="progressbar" 
                     style="width: <?= $progress ?>%" 
                     aria-valuenow="<?= $progress ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
            <div style="position: absolute; top: 0; left: <?= $bronzePos ?>%; width: 2px; height: 30px; background: #cd7f32;"></div>            
            <div style="position: absolute; top: 0; left: <?= $silverPos ?>%; width: 2px; height: 30px; background: #c0c0c0;"></div>
            <div style="position: absolute; top: 0; left: 100%; width: 2px; height: 30px; background: #ffd700;"></div>
        </div>
        
        <div class="row text-center mt-4">
            <div class="col">
                <div class="tier <?= $user['tier'] === 'regular' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i>
                    <p>Regular</p>
                    <small>0+ points</small>
                </div>
            </div>
            <div class="col">
                <div class="tier <?= $user['tier'] === 'bronze' ? 'active' : '' ?>">
                    <i class="fas fa-award bronze"></i>
                    <p>Bronze</p>
                    <small><?= $bronzeThreshold ?>+ points</small>
                    <small>5% Discount</small>
                </div>
            </div>
            <div class="col">
                <div class="tier <?= $user['tier'] === 'silver' ? 'active' : '' ?>">
                    <i class="fas fa-award silver"></i>
                    <p>Silver</p>
                    <small><?= $silverThreshold ?>+ points</small>
                    <small>10% Discount</small>
                </div>
            </div>
            <div class="col">
                <div class="tier <?= $user['tier'] === 'gold' ? 'active' : '' ?>">
                    <i class="fas fa-award gold"></i>
                    <p>Gold</p>
                    <small><?= $goldThreshold ?>+ points</small>
                    <small>15% Discount</small>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h6>How it works:</h6>
            <ul>
                <li>Earn 1 point for every HKD 10 spent</li>
                <li>Current points: <strong><?= $user['points'] ?></strong></li>
                <?php if ($user['tier'] !== 'gold'): ?>
                    <li>
                        <?php 
                        $nextTier = '';
                        $neededPoints = 0;
                        if ($user['tier'] === 'regular') {
                            $nextTier = 'Bronze';
                            $neededPoints = $bronzeThreshold - $user['points'];
                        } elseif ($user['tier'] === 'bronze') {
                            $nextTier = 'Silver';
                            $neededPoints = $silverThreshold - $user['points'];
                        } else {
                            $nextTier = 'Gold';
                            $neededPoints = $goldThreshold - $user['points'];
                        }
                        ?>
                        Need <?= $neededPoints ?> more points for next tier, <span style="font-weight:bold;"><?= $nextTier ?></span> tier
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<style>
.progress-container {
    margin-top: 40px;
    margin-bottom: 40px;
}
.tier {
    padding: 10px;
    border-radius: 5px;
    transition: all 0.3s ease;
}
.tier.active {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.tier i {
    font-size: 24px;
    margin-bottom: 5px;
}
.tier i.bronze { color: #cd7f32; }
.tier i.silver { color: #c0c0c0; }
.tier i.gold { color: #ffd700; }
</style>

<?php require_once '../includes/footer.php'; ?>