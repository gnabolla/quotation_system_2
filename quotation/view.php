<?php
/**
 * View quotation details
 * Path: quotation/view.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/Quotation.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Quotation object
$quotation = new Quotation($db);

// Set ID of quotation to be viewed
$quotation->quotation_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Read the details of quotation
if (!$quotation->readOne()) {
    // Redirect to list page if ID doesn't exist
    header("Location: list.php");
    exit();
}

// Page title
$page_title = "View Quotation #" . $quotation->quotation_id;

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Quotation #<?php echo $quotation->quotation_id; ?></h2>
                <div>
                    <a href="list.php" class="btn btn-secondary">Back to List</a>
                    <a href="edit.php?id=<?php echo $quotation->quotation_id; ?>" class="btn btn-warning">Edit</a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="export.php?id=<?php echo $quotation->quotation_id; ?>&type=csv">CSV</a></li>
                            <li><a class="dropdown-item" href="export.php?id=<?php echo $quotation->quotation_id; ?>&type=excel">Excel</a></li>
                            <li><a class="dropdown-item" href="export.php?id=<?php echo $quotation->quotation_id; ?>&type=pdf">PDF</a></li>
                        </ul>
                    </div>
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
                            <strong>Customer:</strong> <?php echo htmlspecialchars($quotation->customer_name); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Email:</strong> <?php echo htmlspecialchars($quotation->customer_email); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Phone:</strong> <?php echo htmlspecialchars($quotation->customer_phone); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong> 
                            <span class="badge <?php 
                                echo ($quotation->status == 'draft') ? 'bg-secondary' : 
                                    (($quotation->status == 'sent') ? 'bg-primary' : 
                                        (($quotation->status == 'accepted') ? 'bg-success' : 'bg-danger')); 
                            ?>">
                                <?php echo ucfirst($quotation->status); ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Quotation Date:</strong> <?php echo $quotation->quotation_date; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Valid Until:</strong> <?php echo $quotation->valid_until; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <strong>Notes:</strong>
                            <p><?php echo nl2br(htmlspecialchars($quotation->notes)); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quotation items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Quotation Items</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item No</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Description</th>
                                    <th>Original Price</th>
                                    <th>Markup (%)</th>
                                    <th>Unit Price</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($quotation->items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['item_no']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td><?php echo number_format($item['original_price'], 2); ?></td>
                                        <td><?php echo number_format($item['markup_percentage'], 2); ?></td>
                                        <td><?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td><?php echo number_format($item['total_amount'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="7" class="text-end fw-bold">Grand Total:</td>
                                    <td><?php echo number_format($quotation->calculateGrandTotal(), 2); ?></td>
                                </tr>
                            </tbody>
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