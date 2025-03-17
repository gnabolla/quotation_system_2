<?php
/**
 * Create new quotation
 * Path: quotation/create.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/Quotation.php';
require_once '../models/QuotationItem.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Quotation object
$quotation = new Quotation($db);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set quotation property values
    $quotation->customer_name = $_POST['customer_name'];
    $quotation->customer_email = $_POST['customer_email'];
    $quotation->customer_phone = $_POST['customer_phone'];
    $quotation->quotation_date = $_POST['quotation_date'];
    $quotation->valid_until = $_POST['valid_until'];
    $quotation->status = $_POST['status'];
    $quotation->notes = $_POST['notes'];
    
    // Process quotation items
    $item_nos = $_POST['item_no'];
    $quantities = $_POST['quantity'];
    $units = $_POST['unit'];
    $descriptions = $_POST['description'];
    $original_prices = $_POST['original_price'];
    $markup_percentages = $_POST['markup_percentage'];
    $unit_prices = $_POST['unit_price'];
    $total_amounts = $_POST['total_amount'];
    
    // Create items array
    $quotation->items = [];
    for ($i = 0; $i < count($item_nos); $i++) {
        $quotation->items[] = [
            'item_no' => $item_nos[$i],
            'quantity' => $quantities[$i],
            'unit' => $units[$i],
            'description' => $descriptions[$i],
            'original_price' => $original_prices[$i],
            'markup_percentage' => $markup_percentages[$i],
            'unit_price' => $unit_prices[$i],
            'total_amount' => $total_amounts[$i]
        ];
    }
    
    // Create the quotation
    if ($quotation->create()) {
        // Redirect to quotation list
        header("Location: list.php");
        exit();
    } else {
        $error_message = "Unable to create quotation.";
    }
}

// Page title
$page_title = "Create Quotation";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Create Quotation</h2>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="post" id="quotationForm">
                <!-- Quotation details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Quotation Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_email" class="form-label">Customer Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_phone" class="form-label">Customer Phone</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                    <option value="accepted">Accepted</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quotation_date" class="form-label">Quotation Date</label>
                                <input type="date" class="form-control" id="quotation_date" name="quotation_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="valid_until" class="form-label">Valid Until</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Quotation items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Quotation Items</h4>
                        <button type="button" class="btn btn-primary" id="addItemBtn">Add Item</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
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
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-end fw-bold">Grand Total:</td>
                                        <td id="grandTotal">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Save Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template for new item row -->
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <input type="number" class="form-control item-no" name="item_no[]" value="1" min="1" required>
        </td>
        <td>
            <input type="number" class="form-control quantity" name="quantity[]" value="1" min="0.01" step="0.01" required>
        </td>
        <td>
            <input type="text" class="form-control unit" name="unit[]" placeholder="pcs">
        </td>
        <td>
            <input type="text" class="form-control description" name="description[]" required>
        </td>
        <td>
            <input type="number" class="form-control original-price" name="original_price[]" value="0.00" min="0" step="0.01" required>
        </td>
        <td>
            <input type="number" class="form-control markup-percentage" name="markup_percentage[]" value="0" min="0" step="0.01" required>
        </td>
        <td>
            <input type="number" class="form-control unit-price" name="unit_price[]" value="0.00" min="0" step="0.01" readonly>
        </td>
        <td>
            <input type="number" class="form-control total-amount" name="total_amount[]" value="0.00" min="0" step="0.01" readonly>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
        </td>
    </tr>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add first item row
    addItemRow();
    
    // Add item button click handler
    document.getElementById('addItemBtn').addEventListener('click', function() {
        addItemRow();
    });
    
    // Remove item button click handler (delegated)
    document.getElementById('itemsTableBody').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            const row = e.target.closest('tr');
            
            // Only remove if there's more than one row
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                updateItemNumbers();
                calculateGrandTotal();
            } else {
                alert('At least one item is required.');
            }
        }
    });
    
    // Input change handlers (delegated)
    document.getElementById('itemsTableBody').addEventListener('input', function(e) {
        const row = e.target.closest('tr');
        
        if (e.target.classList.contains('original-price') || 
            e.target.classList.contains('markup-percentage') || 
            e.target.classList.contains('quantity')) {
            calculateRowTotals(row);
            calculateGrandTotal();
        }
    });
});

// Add new item row
function addItemRow() {
    const template = document.getElementById('itemRowTemplate');
    const tbody = document.getElementById('itemsTableBody');
    
    // Clone template content
    const clone = document.importNode(template.content, true);
    
    // Update item number based on existing rows
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 0) {
        clone.querySelector('.item-no').value = rows.length + 1;
    }
    
    // Append new row
    tbody.appendChild(clone);
    
    // Setup calculation handlers
    const newRow = tbody.lastElementChild;
    calculateRowTotals(newRow);
}

// Update item numbers for all rows
function updateItemNumbers() {
    const rows = document.querySelectorAll('.item-row');
    rows.forEach((row, index) => {
        row.querySelector('.item-no').value = index + 1;
    });
}

// Calculate totals for a row
function calculateRowTotals(row) {
    const originalPrice = parseFloat(row.querySelector('.original-price').value) || 0;
    const markupPercentage = parseFloat(row.querySelector('.markup-percentage').value) || 0;
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    
    // Calculate unit price (original price + markup)
    const unitPrice = originalPrice * (1 + (markupPercentage / 100));
    row.querySelector('.unit-price').value = unitPrice.toFixed(2);
    
    // Calculate total amount (quantity * unit price)
    const totalAmount = quantity * unitPrice;
    row.querySelector('.total-amount').value = totalAmount.toFixed(2);
}

// Calculate grand total
function calculateGrandTotal() {
    let grandTotal = 0;
    
    document.querySelectorAll('.total-amount').forEach(input => {
        grandTotal += parseFloat(input.value) || 0;
    });
    
    document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
}
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>