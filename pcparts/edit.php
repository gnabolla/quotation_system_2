<?php
/**
 * Edit simplified PC parts quotation
 * Path: pcparts/edit.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/PCPartsQuotation.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize PCPartsQuotation object
$quotation = new PCPartsQuotation($db);

// Set ID of quotation to be edited
$quotation->quotation_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Read the details of quotation
if (!$quotation->readOne()) {
    // Redirect to list page if ID doesn't exist
    header("Location: list.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set quotation property values
    $quotation->quotation_name = $_POST['quotation_name'];
    $quotation->client_name = $_POST['client_name'];
    
    // Process quotation items
    $item_ids = $_POST['item_id'];
    $item_types = $_POST['item_type']; // 'item', 'bundle', or 'custom'
    $descriptions = $_POST['description']; 
    $original_prices = $_POST['original_price'];
    $quantities = $_POST['quantity'];
    
    // Create items array
    $quotation->items = [];
    for ($i = 0; $i < count($item_ids); $i++) {
        if ($quantities[$i] > 0) {
            $quotation->items[] = [
                'item_id' => $item_ids[$i],
                'item_type' => $item_types[$i],
                'description' => $descriptions[$i],
                'original_price' => $original_prices[$i],
                'quantity' => $quantities[$i]
            ];
        }
    }
    
    // Update the quotation
    if ($quotation->update()) {
        // Redirect to view page
        header("Location: view.php?id={$quotation->quotation_id}");
        exit();
    } else {
        $error_message = "Unable to update PC parts quotation.";
    }
}

// Get all available PC parts from the items table
$query = "SELECT i.item_id, i.item_name, i.price, c.category_name, 'item' as item_type 
          FROM items i 
          JOIN categories c ON i.category_id = c.category_id 
          ORDER BY c.category_name, i.item_name";
$stmt = $db->prepare($query);
$stmt->execute();
$all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all available bundles
$query = "SELECT 
            bundle_id as item_id, 
            CONCAT(bundle_type, ' ', processor, ' + ', motherboard) as item_name, 
            price, 
            CONCAT(bundle_type, ' Bundle') as category_name,
            'bundle' as item_type
          FROM bundles
          ORDER BY bundle_type, processor";
$stmt = $db->prepare($query);
$stmt->execute();
$all_bundles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Combine items and bundles for the searchable table
$all_products = array_merge($all_items, $all_bundles);

// Page title
$page_title = "Edit PC Parts Quotation";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Edit PC Parts Quotation #<?php echo $quotation->quotation_id; ?></h2>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="post" id="pcPartsQuotationForm">
                <!-- Quotation details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Quotation Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quotation_name" class="form-label">Quotation Name</label>
                                <input type="text" class="form-control" id="quotation_name" name="quotation_name" value="<?php echo htmlspecialchars($quotation->quotation_name); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">Client Name</label>
                                <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($quotation->client_name); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search and Add Item Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Search & Add PC Parts or Bundles</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search for parts or bundles (by name, category, processor, etc.)">
                                    <button type="button" class="btn btn-primary" id="searchButton">Search</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                    <table class="table table-bordered table-hover" id="partsTable">
                                        <thead class="position-sticky" style="top: 0; background: white; z-index: 10;">
                                            <tr>
                                                <th>Item/Bundle</th>
                                                <th>Category/Type</th>
                                                <th>Price</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_products as $product): ?>
                                            <tr class="<?php echo $product['item_type'] == 'bundle' ? 'table-info' : ''; ?>">
                                                <td><?php echo htmlspecialchars($product['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-success btn-sm select-item" 
                                                        data-id="<?php echo $product['item_id']; ?>" 
                                                        data-name="<?php echo htmlspecialchars($product['item_name']); ?>"
                                                        data-price="<?php echo $product['price']; ?>"
                                                        data-type="<?php echo $product['item_type']; ?>">
                                                        Select
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Custom Item Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Add Custom Item</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-5 mb-3">
                                                <label for="customItemName" class="form-label">Item Name/Description</label>
                                                <input type="text" class="form-control" id="customItemName">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="customItemPrice" class="form-label">Price (₱)</label>
                                                <input type="number" class="form-control" id="customItemPrice" min="0" step="0.01">
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label for="customItemQuantity" class="form-label">Quantity</label>
                                                <input type="number" class="form-control" id="customItemQuantity" min="1" value="1">
                                            </div>
                                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                                <button type="button" id="addCustomItemButton" class="btn btn-primary w-100">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quotation items -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Quotation Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Original Price</th>
                                        <th>Quantity</th>
                                        <th>Total Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <?php foreach ($quotation->items as $index => $item): 
                                        $rowClass = $item['item_type'] === 'bundle' ? 'class="table-info"' : '';
                                        $total_price = floatval($item['original_price']) * floatval($item['quantity']);
                                    ?>
                                    <tr id="item_row_<?php echo $index; ?>" <?php echo $rowClass; ?>>
                                        <td><?php echo htmlspecialchars($item['description']); ?>
                                            <input type="hidden" name="item_id[]" value="<?php echo $item['item_id']; ?>">
                                            <input type="hidden" name="item_type[]" value="<?php echo $item['item_type']; ?>">
                                            <input type="hidden" name="description[]" value="<?php echo htmlspecialchars($item['description']); ?>">
                                        </td>
                                        <td class="original-price">
                                            ₱<?php echo number_format($item['original_price'], 2); ?>
                                            <input type="hidden" name="original_price[]" value="<?php echo $item['original_price']; ?>">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantity-input" name="quantity[]" value="<?php echo $item['quantity']; ?>" min="1" onchange="updateRowTotal(<?php echo $index; ?>)">
                                        </td>
                                        <td class="total-price">₱<?php echo number_format($total_price, 2); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(<?php echo $index; ?>)">Remove</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                        <td id="grand_total">₱0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <a href="view.php?id=<?php echo $quotation->quotation_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" id="submit_btn" class="btn btn-success">Update Quotation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for items data -->
<script>
// Store all items data for easy access
const productsData = <?php echo json_encode($all_products); ?>;
let itemCount = <?php echo count($quotation->items); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', filterItems);
    document.getElementById('searchButton').addEventListener('click', filterItems);
    
    // Select item buttons
    document.querySelectorAll('.select-item').forEach(button => {
        button.addEventListener('click', selectItem);
    });
    
    // Add custom item button
    document.getElementById('addCustomItemButton').addEventListener('click', addCustomItem);
    
    // Calculate totals on load
    calculateTotals();
});

// Filter items based on search input
function filterItems() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#partsTable tbody tr');
    
    rows.forEach(row => {
        const item = row.getElementsByTagName('td')[0].textContent.toLowerCase();
        const category = row.getElementsByTagName('td')[1].textContent.toLowerCase();
        
        if (item.includes(searchValue) || category.includes(searchValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Handle item selection
function selectItem(event) {
    // Get item data from button attributes
    const button = event.currentTarget;
    const itemId = button.getAttribute('data-id');
    const itemName = button.getAttribute('data-name');
    const itemPrice = parseFloat(button.getAttribute('data-price'));
    const itemType = button.getAttribute('data-type');
    
    // Add item to quotation
    addItemToQuotation(itemId, itemName, itemPrice, 1, itemType);
}

// Add custom item
function addCustomItem() {
    const itemName = document.getElementById('customItemName').value.trim();
    const itemPrice = parseFloat(document.getElementById('customItemPrice').value) || 0;
    const itemQuantity = parseInt(document.getElementById('customItemQuantity').value) || 1;
    
    if (itemName === '' || itemPrice === 0) {
        alert('Please enter item name and price.');
        return;
    }
    
    // Add custom item to quotation with a custom ID (negative to avoid conflicts)
    addItemToQuotation(-itemCount-1, itemName, itemPrice, itemQuantity, 'custom');
    
    // Clear custom item form
    document.getElementById('customItemName').value = '';
    document.getElementById('customItemPrice').value = '';
    document.getElementById('customItemQuantity').value = '1';
}

// Add item to quotation table
function addItemToQuotation(itemId, itemName, itemPrice, itemQuantity, itemType) {
    const totalPrice = itemPrice * itemQuantity;
    
    // Create table row
    const tableBody = document.getElementById('itemsTableBody');
    itemCount++;
    
    const rowClass = itemType === 'bundle' ? 'class="table-info"' : '';
    
    const row = `
        <tr id="item_row_${itemCount}" ${rowClass}>
            <td>${itemName}
                <input type="hidden" name="item_id[]" value="${itemId}">
                <input type="hidden" name="item_type[]" value="${itemType}">
                <input type="hidden" name="description[]" value="${itemName}">
            </td>
            <td class="original-price">
                ₱${itemPrice.toFixed(2)}
                <input type="hidden" name="original_price[]" value="${itemPrice.toFixed(2)}">
            </td>
            <td>
                <input type="number" class="form-control quantity-input" name="quantity[]" value="${itemQuantity}" min="1" onchange="updateRowTotal(${itemCount})">
            </td>
            <td class="total-price">₱${totalPrice.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${itemCount})">Remove</button>
            </td>
        </tr>
    `;
    
    tableBody.insertAdjacentHTML('beforeend', row);
    
    // Clear search
    document.getElementById('searchInput').value = '';
    filterItems();
    
    // Recalculate totals
    calculateTotals();
}

// Update row total when quantity changes
function updateRowTotal(rowId) {
    const row = document.getElementById(`item_row_${rowId}`);
    if (!row) return;
    
    const originalPriceText = row.querySelector('.original-price').textContent;
    const originalPrice = parseFloat(originalPriceText.replace('₱', ''));
    const quantity = parseInt(row.querySelector('.quantity-input').value);
    
    const totalPrice = originalPrice * quantity;
    
    row.querySelector('.total-price').textContent = `₱${totalPrice.toFixed(2)}`;
    
    calculateTotals();
}

// Remove row from table
function removeRow(rowId) {
    const row = document.getElementById(`item_row_${rowId}`);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

// Calculate grand total
function calculateTotals() {
    let grandTotal = 0;
    
    // Get all rows and calculate totals
    const rows = document.querySelectorAll('#itemsTableBody tr');
    rows.forEach(row => {
        const totalPriceText = row.querySelector('.total-price').textContent;
        const totalPrice = parseFloat(totalPriceText.replace('₱', ''));
        grandTotal += totalPrice;
    });
    
    // Update display
    document.getElementById('grand_total').textContent = `₱${grandTotal.toFixed(2)}`;
}
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>