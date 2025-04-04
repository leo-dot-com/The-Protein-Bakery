<?php
require_once '../includes/header.php';
require_once '../includes/db_connection.php';
require_once '../includes/auth_functions.php';
require_login();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_address'])) {
        // Add new address
        $stmt = $pdo->prepare("
            INSERT INTO user_addresses 
            (user_id, address_line1, address_line2, city, postal_code, is_default)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $isDefault = isset($_POST['is_default']) ? 1 : 0;
        
        // If setting as default, first unset any existing default
        if ($isDefault) {
            $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")
               ->execute([$_SESSION['user_id']]);
        }
        
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['address_line1'],
            $_POST['address_line2'] ?? '',
            $_POST['city'],
            $_POST['postal_code'],
            $isDefault
        ]);
        
        $_SESSION['success'] = "Address added successfully";
        header("Location: addresses.php");
        exit;
    }
    elseif (isset($_POST['delete_address'])) {
        // Delete address
        $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['address_id'], $_SESSION['user_id']]);
        
        $_SESSION['success'] = "Address deleted successfully";
        header("Location: addresses.php");
        exit;
    }
    elseif (isset($_POST['set_default'])) {
        // Set default address
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?")
               ->execute([$_SESSION['user_id']]);
            
            $pdo->prepare("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?")
               ->execute([$_POST['address_id'], $_SESSION['user_id']]);
            
            $pdo->commit();
            $_SESSION['success'] = "Default address updated";
            header("Location: addresses.php");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Failed to update default address";
        }
    }
}

// Get user addresses
$addresses = $pdo->prepare("
    SELECT * FROM user_addresses 
    WHERE user_id = ? 
    ORDER BY is_default DESC, id ASC
");
$addresses->execute([$_SESSION['user_id']]);
$userAddresses = $addresses->fetchAll();
?>

<div class="container py-5">
    <div class="row">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Addresses</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="fas fa-plus me-2"></i>Add New Address
                </button>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($userAddresses)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> You haven't saved any addresses yet.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($userAddresses as $address): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 <?php echo $address['is_default'] ? 'border-primary' : ''; ?>">
                            <div class="card-body">
                                <?php if ($address['is_default']): ?>
                                    <span class="badge bg-primary mb-2">Default Address</span>
                                <?php endif; ?>
                                
                                <h5 class="card-title"><?php echo htmlspecialchars($address['address_line1']); ?></h5>
                                <?php if (!empty($address['address_line2'])): ?>
                                    <p class="card-text"><?php echo htmlspecialchars($address['address_line2']); ?></p>
                                <?php endif; ?>
                                <p class="card-text">
                                    <?php echo htmlspecialchars($address['city']); ?><br>
                                    <?php echo htmlspecialchars($address['postal_code']); ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                    <?php if (!$address['is_default']): ?>
                                        <button type="submit" name="set_default" class="btn btn-sm btn-outline-primary me-2">
                                            Set as Default
                                        </button>
                                    <?php endif; ?>
                                    <button type="submit" name="delete_address" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this address?');">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAddressModalLabel">Add New Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="address_line1" class="form-label">Address Line 1</label>
                        <input type="text" class="form-control" id="address_line1" name="address_line1" required>
                    </div>
                    <div class="mb-3">
                        <label for="address_line2" class="form-label">Address Line 2 (Optional)</label>
                        <input type="text" class="form-control" id="address_line2" name="address_line2">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">Set as default address</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_address" class="btn btn-primary">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>