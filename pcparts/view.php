<?php
/**
 * View simplified PC parts quotation
 * Path: pcparts/view.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/PCPartsQuotation.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize PCPartsQuotation object
$quotation = new PCPartsQuotation($db);

// Set ID of quotation to be viewed
$quotation->quotation_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Read the details of quotation
if (!$quotation->readOne()) {
    // Redirect to list page if ID doesn't exist
    header("Location: list.php");
    exit();
}

// Calculate grand total
$grand_total = $quotation->calculateGrandTotal();

// Page title
$page_title = "View PC Parts Quotation";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>PC Parts Quotation #<?php echo $quotation->quotation_id; ?></h2>
                <div>
                    <a href="list.php" class="btn btn-secondary">Back to List</a>
                    <a href="edit.php?id=<?php echo $quotation->quotation_id; ?>" class="btn btn-warning">Edit</a>
                    <button onclick="window.print();" class="btn btn-info">Print</button>
                    <a href="delete.php?id=<?php echo $quotation->quotation_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this quotation?')">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <!-- Quotation details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Quotation Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Quotation Name:</strong> <?php echo htmlspecialchars($quotation->quotation_name); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Client Name:</strong> <?php echo htmlspecialchars($quotation->client_name); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date Created:</strong> <?php echo date('F j, Y', strtotime($quotation->created_at)); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Last Updated:</strong> <?php echo date('F j, Y', strtotime($quotation->updated_at)); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotation items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>PC Parts</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Original Price</th>
                                    <th>Quantity</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($quotation->items)): ?>
                                    <?php foreach ($quotation->items as $item): 
                                        $total_price = floatval($item['original_price']) * floatval($item['quantity']);
                                    ?>
                                    <tr class="<?php echo $item['item_type'] === 'bundle' ? 'table-info' : ''; ?>">
                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td>₱<?php echo number_format($item['original_price'], 2); ?></td>
                                        <td><?php echo number_format($item['quantity'], 0); ?></td>
                                        <td>₱<?php echo number_format($total_price, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No items found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                    <td>₱<?php echo number_format($grand_total, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>