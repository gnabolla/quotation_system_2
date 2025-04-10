<?php
/**
 * PC Builder main page
 * Path: pcbuilder/index.php
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

// Handle reset action
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    $pcBuilder->resetConfiguration();
    $pcBuilder->saveToSession();
    header("Location: index.php");
    exit();
}

// Calculate current total
$total_price = $pcBuilder->calculateTotalPrice();

// Page title
$page_title = "PC Builder";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>PC Builder</h2>
                <div>
                    <a href="index.php?action=reset" class="btn btn-warning" onclick="return confirm('Are you sure you want to reset your configuration?')">Reset Configuration</a>
                    <a href="quotation.php" class="btn btn-success">Create Quotation</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <!-- Current Configuration Summary -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Your PC Configuration</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>Total Price: ₱<?php echo number_format($total_price, 2); ?></h5>
                        <div>
                            <a href="bundle.php" class="btn btn-primary">Bundle</a>
                            <a href="items.php" class="btn btn-info">Add Parts</a>
                            <a href="custom.php" class="btn btn-secondary">Add Custom Item</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pcBuilder->configuration['bundle'])): 
                                    $bundle = $pcBuilder->configuration['bundle'];
                                ?>
                                <tr>
                                    <td>Bundle</td>
                                    <td><?php echo $bundle['bundle_type'] . ': ' . $bundle['processor'] . ' + ' . $bundle['motherboard']; ?></td>
                                    <td>₱<?php echo number_format($bundle['price'], 2); ?></td>
                                    <td>1</td>
                                    <td>₱<?php echo number_format($bundle['price'], 2); ?></td>
                                    <td>
                                        <a href="update.php?action=remove_bundle" class="btn btn-danger btn-sm">Remove</a>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <a href="bundle.php" class="btn btn-outline-primary">Select a Bundle (Processor + Motherboard)</a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pcBuilder->configuration['items'])): 
                                    foreach ($pcBuilder->configuration['items'] as $item):
                                        $subtotal = $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item['category_name']; ?></td>
                                    <td><?php echo $item['item_name']; ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <form action="update.php" method="post" class="d-flex">
                                            <input type="hidden" name="action" value="update_quantity">
                                            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                            <input type="hidden" name="type" value="regular">
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm" style="width: 60px;">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary ms-1">Update</button>
                                        </form>
                                    </td>
                                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                                    <td>
                                        <a href="update.php?action=remove_item&item_id=<?php echo $item['item_id']; ?>&type=regular" class="btn btn-danger btn-sm">Remove</a>
                                    </td>
                                </tr>
                                <?php endforeach;
                                endif; ?>
                                
                                <?php if (!empty($pcBuilder->configuration['custom_items'])): 
                                    foreach ($pcBuilder->configuration['custom_items'] as $item):
                                        $subtotal = $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item['category_name']; ?> (Custom)</td>
                                    <td><?php echo $item['item_name']; ?></td>
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
                                <?php endforeach;
                                endif; ?>
                                
                                <?php if (empty($pcBuilder->configuration['items']) && empty($pcBuilder->configuration['custom_items']) && empty($pcBuilder->configuration['bundle'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <p class="text-muted mb-0">Your configuration is empty. Please add a bundle or components to get started.</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td colspan="2" class="fw-bold">₱<?php echo number_format($total_price, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <?php if (!empty($pcBuilder->configuration['bundle']) || !empty($pcBuilder->configuration['items']) || !empty($pcBuilder->configuration['custom_items'])): ?>
                    <div class="text-center mt-3">
                        <a href="quotation.php" class="btn btn-lg btn-success">Create Quotation from this Configuration</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>