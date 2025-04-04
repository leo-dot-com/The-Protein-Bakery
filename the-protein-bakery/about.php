<?php
$pageTitle = "About Us";
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-6">
            <h1 class="display-4 mb-4">About The Protein Bakery</h1>
            <p class="lead">Homemade protein bars crafted with passion and premium ingredients.</p>
            
            <p>Founded in 2023, The Protein Bakery started as a small home kitchen experiment to create delicious, 
            protein-packed snacks that don't compromise on taste or quality.</p>
            
            <h4 class="mt-5">Our Mission</h4>
            <p>To provide fitness enthusiasts and health-conscious individuals with convenient, 
            nutritious snacks that support their active lifestyles without artificial additives or preservatives.</p>
            
            <h4 class="mt-5">Our Ingredients</h4>
            <ul>
                <li>100% natural ingredients</li>
                <li>High-quality protein sources</li>
                <li>No artificial sweeteners</li>
                <li>Locally sourced when possible</li>
            </ul>
        </div>
        <div class="col-lg-6">
            <img src="/assets/images/about.jpg" class="img-fluid rounded" alt="Our Kitchen">
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Why Choose Us?</h2>
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                    <h4>Made with Love</h4>
                    <p>Every bar is handmade in small batches for quality control.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-leaf fa-3x text-primary mb-3"></i>
                    <h4>Natural Ingredients</h4>
                    <p>We use only wholesome, recognizable ingredients.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="fas fa-bolt fa-3x text-primary mb-3"></i>
                    <h4>Performance Fuel</h4>
                    <p>Perfect for pre-workout energy or post-workout recovery.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>