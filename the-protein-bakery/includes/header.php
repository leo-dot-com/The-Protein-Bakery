<?php
// /the-protein-bakery/includes/header.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Protein Bakery - <?php echo $pageTitle ?? 'Homemade Protein Bars'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/the-protein-bakery/index.php">
                <img src="/protein-bakery/assets/images/logo.png" alt="The Protein Bakery" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/the-protein-bakery/index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/the-protein-bakery/products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="/the-protein-bakery/about.php">About</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <!-- Cart Icon (Always Visible) -->
                    <a href="/the-protein-bakery/cart.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-shopping-cart"></i> 
                        <span class="badge bg-primary">
                            <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                        </span>
                    </a>
                    
                    <!-- User Account Dropdown -->
                    <div class="dropdown">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Logged In State -->
                            <button class="btn btn-outline-light dropdown-toggle" type="button" 
                                    id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>
                                <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/the-protein-bakery/account/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="/the-protein-bakery/account/orders.php">
                                    <i class="fas fa-box-open me-2"></i>My Orders
                                </a></li>
                                <li><a class="dropdown-item" href="/the-protein-bakery/account/addresses.php">
                                    <i class="fas fa-map-marker-alt me-2"></i>Saved Addresses
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/the-protein-bakery/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        <?php else: ?>
                            <!-- Guest State -->
                            <a href="/the-protein-bakery/auth/login.php" class="btn btn-outline-light">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <main class="container my-5">
