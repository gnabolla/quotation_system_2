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
                    <?php
                    // Add these buttons to quotation/view.php in the button group after "Export" dropdown
                    // Put this code right before the closing </div> of the button group
                    ?>

                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Delivery
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../delivery/create.php?quotation_id=<?php echo $quotation->quotation_id; ?>">Create Delivery Receipt</a></li>
                            <li><a class="dropdown-item" href="../delivery/list.php?quotation_id=<?php echo $quotation->quotation_id; ?>">View All Delivery Receipts</a></li>
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
                                                echo ($quotation->status == 'draft') ? 'bg-secondary' : (($quotation->status == 'sent') ? 'bg-primary' : (($quotation->status == 'accepted') ? 'bg-success' : 'bg-danger'));
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
                                <?php foreach ($quotation->items as $item): ?>
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
    <?php
    // Also add a section at the bottom of the quotation view page to show delivery receipts
    // Add this code before the closing </div> of the container div
    ?>

    <!-- Delivery receipts related to this quotation -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Delivery Receipts</h4>
            <a href="../delivery/create.php?quotation_id=<?php echo $quotation->quotation_id; ?>" class="btn btn-primary btn-sm">Create Delivery Receipt</a>
        </div>
        <div class="card-body">
            <?php
            // Include delivery receipt model
            require_once '../models/DeliveryReceipt.php';

            // Get delivery receipts for this quotation
            $delivery = new DeliveryReceipt($db);
            $delivery->quotation_id = $quotation->quotation_id;
            $stmt = $delivery->readByQuotationId();
            $num = $stmt->rowCount();

            if ($num > 0) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered table-striped">';
                echo '<thead>';
                echo '<tr>';
                echo '<th>Receipt ID</th>';
                echo '<th>Recipient</th>';
                echo '<th>Delivery Date</th>';
                echo '<th>Status</th>';
                echo '<th>Actions</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . $row['receipt_id'] . '</td>';
                    echo '<td>' . htmlspecialchars($row['recipient_name']) . '</td>';
                    echo '<td>' . $row['delivery_date'] . '</td>';
                    echo '<td>';
                    echo '<span class="badge ';
                    echo ($row['delivery_status'] == 'pending') ? 'bg-warning' : (($row['delivery_status'] == 'in_transit') ? 'bg-primary' : (($row['delivery_status'] == 'delivered') ? 'bg-success' : 'bg-danger'));
                    echo '">';
                    echo ucfirst(str_replace('_', ' ', $row['delivery_status']));
                    echo '</span>';
                    echo '</td>';
                    echo '<td>';
                    echo '<a href="../delivery/view.php?id=' . $row['receipt_id'] . '" class="btn btn-info btn-sm">View</a> ';
                    echo '<a href="../delivery/edit.php?id=' . $row['receipt_id'] . '" class="btn btn-warning btn-sm">Edit</a> ';
                    echo '<a href="../delivery/delete.php?id=' . $row['receipt_id'] . '&redirect_to=quotation" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this delivery receipt?\')">Delete</a> ';
                    echo '<a href="../delivery/view.php?id=' . $row['receipt_id'] . '" class="btn btn-primary btn-sm" onclick="window.open(this.href, \'_blank\'); return false;">Print</a>';
                    echo '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-info">No delivery receipts found for this quotation.</div>';
            }
            ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>