<?php
/**
 * PC Builder custom item page
 * Path: pcbuilder/custom.php
 */

// Start session
session_start();

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/PCBuilder.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize PCBuilder object
$pcBuilder = new PCBuilder($db);

// Load configuration from session if exists
$pcBuilder->loadFromSession();

// Get all categories for dropdown
$categories_stmt = $pcBuilder->getCategories();
$categories = [];
while ($row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row;
}

// Process form submission
$success_message = null;
$error_message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    $category_name = $_POST['category_name'] ?? 'Custom';
    
    if (empty($item_name)) {
        $error_message = "Item name is required.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error_message = "Price must be a positive number.";
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $error_message = "Quantity must be a positive number.";
    } else {
        // Add custom item to configuration
        if ($pcBuilder->addCustomItem($item_name, $price, $quantity, $category_name)) {
            // Save to session
            $pcBuilder->saveToSession();
            $success_message = "Custom item added successfully.";
        } else {
            $error_message = "Failed to add custom item.";
        }
    }
}

// Page title
$page_title = "Add Custom Item";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Add Custom Item</h2>
                <a href="index.php" class="btn btn-secondary">Back to Builder</a>
            </div>
        </div>
    </div>

    <?php if ($success_message): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h4>Add Custom Item</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="custom.php">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name*</label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required>
                            <div class="form-text">Enter the name of the item that is not in our inventory.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" list="categories" value="Custom">
                            <datalist id="categories">
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_name']; ?>">
                                <?php endforeach; ?>
                                <option value="Custom">
                            </datalist>
                            <div class="form-text">Select a category or enter a custom one.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price (₱)*</label>
                                <input type="number" class="form-control" id="price" name="price" min="0.01" step="0.01" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity*</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Custom Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Items Already Added -->
    <?php if (!empty($pcBuilder->configuration['custom_items'])): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4>Your Custom Items</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pcBuilder->configuration['custom_items'] as $item): 
                                    $subtotal = $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item['item_name']; ?></td>
                                    <td><?php echo $item['category_name']; ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form action="update.php" method="post" class="d-flex">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                            <input type="hidden" name="type" value="custom">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm" style="width: 60px;">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary ms-1">Update</button>
                                        </form>
                                    </td>
                                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <a href="update.php?action=remove_item&item_id=<?php echo $item['item_id']; ?>&type=custom" class="btn btn-danger btn-sm">Remove</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>