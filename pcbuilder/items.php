<?php
/**
 * PC Builder items selection page
 * Path: pcbuilder/items.php
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

// Get selected category (if any)
$selected_category = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Get all categories
$categories_stmt = $pcBuilder->getCategories();
$categories = [];
while ($row = $categories_stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = $row;
}

// Get items by category
$items_by_category = $pcBuilder->getItemsByCategory($selected_category);

// Page title
$page_title = "Add PC Parts";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Add PC Parts</h2>
                <a href="index.php" class="btn btn-secondary">Back to Builder</a>
            </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <!-- Category selection -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4>Filter by Category</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="items.php" class="btn <?php echo $selected_category === null ? 'btn-primary' : 'btn-outline-primary'; ?>">All Categories</a>
                        <?php foreach ($categories as $category): ?>
                        <a href="items.php?category_id=<?php echo $category['category_id']; ?>" 
                           class="btn <?php echo $selected_category === $category['category_id'] ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo $category['category_name']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items by Category -->
    <?php if (empty($items_by_category)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                No items found for the selected category. Please select a different category or add custom items.
            </div>
        </div>
    </div>
    <?php else: ?>
        <?php foreach ($items_by_category as $category_name => $items): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><?php echo $category_name; ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): 
                                        // Check if item is already in configuration
                                        $in_config = false;
                                        $current_qty = 0;
                                        foreach ($pcBuilder->configuration['items'] as $config_item) {
                                            if ($config_item['item_id'] == $item['item_id']) {
                                                $in_config = true;
                                                $current_qty = $config_item['quantity'];
                                                break;
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $item['item_name']; ?></td>
                                        <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <form action="update.php" method="post" class="d-flex">
                                                <input type="hidden" name="action" value="<?php echo $in_config ? 'update_quantity' : 'add_item'; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                                <input type="hidden" name="type" value="regular">
                                                <input type="number" name="quantity" value="<?php echo $in_config ? $current_qty : 1; ?>" min="1" class="form-control form-control-sm" style="width: 60px;">
                                                <button type="submit" class="btn btn-sm btn-primary ms-1">
                                                    <?php echo $in_config ? 'Update' : 'Add'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if ($in_config): ?>
                                            <a href="update.php?action=remove_item&item_id=<?php echo $item['item_id']; ?>&type=regular" class="btn btn-danger btn-sm">Remove</a>
                                            <?php else: ?>
                                            <a href="update.php?action=add_item&item_id=<?php echo $item['item_id']; ?>&quantity=1" class="btn btn-success btn-sm">Quick Add</a>
                                            <?php endif; ?>
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
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12 text-center mb-4">
            <a href="custom.php" class="btn btn-lg btn-secondary">Can't find what you need? Add a Custom Item</a>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>
        </div>