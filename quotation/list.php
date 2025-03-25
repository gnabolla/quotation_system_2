<?php
/**
 * List all quotations
 * Path: quotation/list.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/Quotation.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Quotation object
$quotation = new Quotation($db);

// Query quotations
$stmt = $quotation->readAll();
$num = $stmt->rowCount();

// Page title
$page_title = "Quotation List";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Quotations</h2>
                <a href="create.php" class="btn btn-primary">Create New Quotation</a>
            </div>

            <?php if($num > 0): ?>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Quotation Number</th>
                            <th>Customer</th>
                            <th>Agency</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['quotation_number'] ?? 'QUO-'.str_pad($row['quotation_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['agency_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['quotation_date'])); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($row['status'] == 'draft') ? 'bg-secondary' : 
                                            (($row['status'] == 'sent') ? 'bg-primary' : 
                                                (($row['status'] == 'accepted') ? 'bg-success' : 'bg-danger')); 
                                    ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $row['quotation_id']; ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="edit.php?id=<?php echo $row['quotation_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete.php?id=<?php echo $row['quotation_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this quotation?')">Delete</a>
                                    <a href="export.php?id=<?php echo $row['quotation_id']; ?>&type=csv" class="btn btn-secondary btn-sm">Export</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No quotations found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once '../includes/footer.php';