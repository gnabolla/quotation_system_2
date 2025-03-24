<?php
/**
 * List all delivery receipts
 * Path: delivery/list.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/DeliveryReceipt.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize DeliveryReceipt object
$delivery = new DeliveryReceipt($db);

// Get quotation ID from URL if exists
$quotation_id = isset($_GET['quotation_id']) ? $_GET['quotation_id'] : null;

// Query deliveries
if ($quotation_id) {
    $delivery->quotation_id = $quotation_id;
    $stmt = $delivery->readByQuotationId();
    $page_title = "Delivery Receipts for Quotation #" . $quotation_id;
} else {
    $stmt = $delivery->readAll();
    $page_title = "All Delivery Receipts";
}

$num = $stmt->rowCount();

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?php echo $page_title; ?></h2>
                <?php if ($quotation_id): ?>
                <div>
                    <a href="../quotation/view.php?id=<?php echo $quotation_id; ?>" class="btn btn-secondary">Back to Quotation</a>
                    <a href="create.php?quotation_id=<?php echo $quotation_id; ?>" class="btn btn-primary">Create New Delivery Receipt</a>
                </div>
                <?php else: ?>
                <a href="../quotation/list.php" class="btn btn-secondary">Back to Quotations</a>
                <?php endif; ?>
            </div>

            <?php if($num > 0): ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Receipt ID</th>
                            <th>Quotation ID</th>
                            <th>Recipient</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['receipt_id']; ?></td>
                                <td><a href="../quotation/view.php?id=<?php echo $row['quotation_id']; ?>">
                                    #<?php echo $row['quotation_id']; ?>
                                </a></td>
                                <td><?php echo htmlspecialchars($row['recipient_name']); ?></td>
                                <td><?php echo $row['delivery_date']; ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($row['delivery_status'] == 'pending') ? 'bg-warning' : 
                                            (($row['delivery_status'] == 'in_transit') ? 'bg-primary' : 
                                                (($row['delivery_status'] == 'delivered') ? 'bg-success' : 'bg-danger')); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $row['delivery_status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['receipt_id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="edit.php?id=<?php echo $row['receipt_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $row['receipt_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this delivery receipt?')">Delete</a>
                                    <a href="generate_pdf.php?id=<?php echo $row['receipt_id']; ?>" class="btn btn-primary btn-sm" target="_blank">PDF</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No delivery receipts found.</div>
                <?php if ($quotation_id): ?>
                <div class="text-center mt-3">
                    <a href="create.php?quotation_id=<?php echo $quotation_id; ?>" class="btn btn-primary">Create Delivery Receipt</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>