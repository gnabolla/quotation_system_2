<?php
/**
 * Copy-friendly quotation details view
 * Path: quotation/copy_details.php
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

// Calculate total
$grand_total = $quotation->calculateGrandTotal();

// Page title
$page_title = "Quotation Details: " . $quotation->quotation_number;

// Include header
include_once '../includes/header.php';

// Format function for better copy-paste experience
function formatCurrency($amount) {
    return number_format($amount, 2, '.', ',');
}
?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Quotation #<?php echo $quotation->quotation_id; ?> - Copy Details</h2>
                <div>
                    <a href="view.php?id=<?php echo $quotation->quotation_id; ?>" class="btn btn-secondary">Back to Quotation</a>
                    <button onclick="copyToClipboard('quotation-details')" class="btn btn-primary">Copy All</button>
                    <button onclick="window.print()" class="btn btn-info">Print</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Quotation Details - Copy Format</h4>
        </div>
        <div class="card-body">
            <div id="quotation-details" class="p-3 bg-light rounded" style="font-family: monospace; white-space: pre-wrap;">
QUOTATION #: <?php echo $quotation->quotation_number; ?>

Date: <?php echo $quotation->quotation_date; ?>
Valid Until: <?php echo $quotation->valid_until; ?>

Customer: <?php echo $quotation->customer_name; ?>
<?php if (!empty($quotation->customer_email)): ?>Email: <?php echo $quotation->customer_email; ?>
<?php endif; ?>
<?php if (!empty($quotation->customer_phone)): ?>Phone: <?php echo $quotation->customer_phone; ?>
<?php endif; ?>

ITEMS:
<?php 
$item_details = ""; 
$max_desc_length = 0;
$max_qty_price_length = 0;

// First pass to determine column widths
foreach ($quotation->items as $item) {
    $desc_length = mb_strlen($item['description']);
    $max_desc_length = max($max_desc_length, $desc_length);
    
    $qty_str = number_format($item['quantity'], 2);
    $unit_price_str = formatCurrency($item['unit_price']);
    $total_str = formatCurrency($item['total_amount']);
    
    $qty_price_length = mb_strlen($qty_str . ' x PHP ' . $unit_price_str);
    $max_qty_price_length = max($max_qty_price_length, $qty_price_length);
}

// Second pass to format the items
foreach ($quotation->items as $idx => $item) {
    $item_no = $idx + 1;
    $desc = $item['description'];
    $qty = number_format($item['quantity'], 2);
    $unit_price = formatCurrency($item['unit_price']);
    $total = formatCurrency($item['total_amount']);
    
    // Format with equal spacing
    $item_details .= "{$item_no}. {$desc}\n";
    $item_details .= "   {$qty} x PHP {$unit_price} = PHP {$total}\n";
    
    // Add unit if available
    if (!empty($item['unit'])) {
        $item_details .= "   Unit: {$item['unit']}\n";
    }
    
    // Add markup info if it's not 0
    if ($item['markup_percentage'] > 0) {
        $item_details .= "   (Includes {$item['markup_percentage']}% markup)\n";
    }
    
    $item_details .= "\n";
}

echo $item_details;
?>
GRAND TOTAL: PHP <?php echo formatCurrency($grand_total); ?>

<?php if (!empty($quotation->notes)): ?>
Notes:
<?php echo $quotation->notes; ?>
<?php endif; ?>

Thank you for your business!
TEKSTORE Computer Parts and Accessories Trading
Magsaysay St., Bantug, Roxas, Isabela
Contact: 09166027454 | Email: tekstore.solution@gmail.com
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info">
                <strong>Tip:</strong> Click the "Copy All" button to copy the entire quotation details to your clipboard. You can then paste it into an email or any other document.
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const text = document.getElementById(elementId).innerText;
    
    // Create temporary element
    const tempElement = document.createElement('textarea');
    tempElement.value = text;
    document.body.appendChild(tempElement);
    
    // Select and copy
    tempElement.select();
    document.execCommand('copy');
    
    // Clean up
    document.body.removeChild(tempElement);
    
    // Provide feedback
    alert('Quotation details copied to clipboard!');
}
</script>

<style>
/* Print styles */
@media print {
    .d-print-none, .btn, nav, footer {
        display: none !important;
    }
    #quotation-details {
        background-color: white !important;
        border: none !important;
    }
    .container {
        width: 100% !important;
        max-width: 100% !important;
    }
    .card {
        border: none !important;
    }
    .card-header {
        background-color: white !important;
        border-bottom: 1px solid #dee2e6 !important;
    }
    body {
        font-size: 12pt;
    }
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?>