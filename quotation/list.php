<?php
// File: quotation/list.php
require_once '../config/db_connection.php';
require_once '../models/Quotation.php';

$database = new Database();
$db = $database->getConnection();
$quotation = new Quotation($db);

// --- Filtering and Tax ---
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : 'all'; // Default to 'all'
// Ensure tax percentage is treated as a float, default to 0
$taxPercentage = isset($_GET['tax_percentage']) && is_numeric($_GET['tax_percentage']) ? floatval($_GET['tax_percentage']) : 0;

// Fetch quotations based on filter
$stmt = $quotation->readAll($selectedStatus);
$num = $stmt ? $stmt->rowCount() : 0; // Check if stmt is valid before rowCount

$page_title = "Quotation List";
include_once '../includes/header.php'; // Include header early

// --- Initialize Totals ---
$totalNetIncomeBeforeTax = 0;
$totalNetIncomeAfterTax = 0;
$quotationsData = []; // Store rows for calculation after fetching

// Fetch all data first to calculate totals correctly
if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $quotationsData[] = $row;
        // Ensure net_income is treated as a float
        $netIncome = isset($row['net_income']) && is_numeric($row['net_income']) ? floatval($row['net_income']) : 0;
        $totalNetIncomeBeforeTax += $netIncome;
    }
} else {
    // Display error only if header hasn't been included yet, otherwise log it
    error_log("Error fetching quotation data.");
    // Optional: Display a user-friendly message if needed, check if headers already sent
    if (!headers_sent()) {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Error fetching quotation data. Please try again later.</div></div>";
    }
}


// Calculate total net income after tax
$taxRate = $taxPercentage / 100;
// Ensure calculation is done correctly, prevent negative results if tax > 100% (though input max is 100)
$totalNetIncomeAfterTax = $totalNetIncomeBeforeTax * max(0, (1 - $taxRate));

?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Quotations</h2>
                <a href="create.php" class="btn btn-primary">Create New Quotation</a>
            </div>

            <!-- Filter and Tax Form -->
            <div class="card mb-4">
                <div class="card-body bg-light">
                    <form method="GET" action="list.php" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Filter by Status:</label>
                            <select name="status" id="status" class="form-select">
                                <option value="all" <?php echo ($selectedStatus == 'all') ? 'selected' : ''; ?>>All Statuses</option>
                                <option value="draft" <?php echo ($selectedStatus == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                <option value="sent" <?php echo ($selectedStatus == 'sent') ? 'selected' : ''; ?>>Sent</option>
                                <option value="accepted" <?php echo ($selectedStatus == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                                <option value="rejected" <?php echo ($selectedStatus == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tax_percentage" class="form-label">Apply Tax (%):</label>
                            <input type="number" name="tax_percentage" id="tax_percentage" class="form-control"
                                   value="<?php echo htmlspecialchars(number_format($taxPercentage, 2)); // Format display for consistency ?>" min="0" max="100" step="0.01" placeholder="e.g., 12.00">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-info w-100">Apply Filters / Tax</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Total Net Income Display -->
            <div class="card mb-4">
                 <div class="card-header">
                    Filtered Net Income Summary
                 </div>
                 <div class="card-body">
                    <div class="row text-center">
                         <div class="col-md-4">
                             <strong>Status Filter:</strong><br> <?php echo ucfirst(htmlspecialchars($selectedStatus)); ?>
                         </div>
                         <div class="col-md-4">
                             <strong>Total Net Income (Before Tax):</strong><br> <?php echo number_format($totalNetIncomeBeforeTax, 2); ?>
                         </div>
                         <div class="col-md-4">
                              <strong>Total Net Income (After <?php echo number_format($taxPercentage, 2); ?>% Tax):</strong><br> <?php echo number_format($totalNetIncomeAfterTax, 2); ?>
                         </div>
                    </div>
                 </div>
            </div>


            <?php if(!empty($quotationsData)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Quotation Number</th>
                                <th>Customer</th>
                                <th>Agency</th>
                                <th>Date</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Net Income</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quotationsData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['quotation_number'] ?? 'QUO-'.str_pad($row['quotation_id'], 3, '0', STR_PAD_LEFT)); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['agency_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['quotation_date'])); ?></td>
                                    <td class="text-center">
                                        <span class="badge <?php
                                            echo ($row['status'] == 'draft') ? 'bg-secondary' :
                                                (($row['status'] == 'sent') ? 'bg-primary' :
                                                    (($row['status'] == 'accepted') ? 'bg-success' : 'bg-danger'));
                                        ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php
                                            $netIncome = isset($row['net_income']) && is_numeric($row['net_income']) ? floatval($row['net_income']) : 0;
                                            echo number_format($netIncome, 2);
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="view.php?id=<?php echo $row['quotation_id']; ?>" class="btn btn-info btn-sm" title="View">View</a>
                                        <a href="edit.php?id=<?php echo $row['quotation_id']; ?>" class="btn btn-warning btn-sm" title="Edit">Edit</a>
                                        <a href="delete.php?id=<?php echo $row['quotation_id']; ?>" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this quotation and all associated items/deliveries?')">Delete</a>
                                        <a href="export.php?id=<?php echo $row['quotation_id']; ?>&type=csv" class="btn btn-secondary btn-sm" title="Export CSV">CSV</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($num === 0): // Use strict comparison for 0 results vs error ?>
                 <div class="alert alert-info">No quotations found matching the current filter criteria.</div>
            <?php else: // This case might occur if $stmt was false initially ?>
                 <div class="alert alert-warning">Could not display quotations. Please check system logs or contact support.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include_once '../includes/footer.php'; // Include footer at the end
?>