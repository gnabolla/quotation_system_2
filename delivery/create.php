<?php
/**
 * Create delivery receipt
 * Path: delivery/create.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/Quotation.php';
require_once '../models/DeliveryReceipt.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Quotation object
$quotation = new Quotation($db);

// Set ID of quotation
$quotation_id = isset($_GET['quotation_id']) ? $_GET['quotation_id'] : die('ERROR: Missing Quotation ID.');

// Read the details of quotation
$quotation->quotation_id = $quotation_id;
if (!$quotation->readOne()) {
    // Redirect to list page if ID doesn't exist
    header("Location: ../quotation/list.php");
    exit();
}

// Initialize DeliveryReceipt object
$delivery = new DeliveryReceipt($db);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set delivery receipt property values
    $delivery->quotation_id = $quotation_id;
    $delivery->delivery_date = $_POST['delivery_date'];
    $delivery->recipient_name = $_POST['recipient_name'];
    $delivery->recipient_position = $_POST['recipient_position'];
    $delivery->delivery_address = $_POST['delivery_address'];
    $delivery->delivery_notes = $_POST['delivery_notes'];
    $delivery->delivery_status = $_POST['delivery_status'];
    
    // Process delivery items
    $delivery->items = [];
    if (isset($_POST['item_id']) && is_array($_POST['item_id'])) {
        $item_ids = $_POST['item_id'];
        $quantities = $_POST['quantity_delivered'];
        
        for ($i = 0; $i < count($item_ids); $i++) {
            if ($quantities[$i] > 0) {
                $delivery->items[] = [
                    'item_id' => $item_ids[$i],
                    'quantity_delivered' => $quantities[$i]
                ];
            }
        }
    }
    
    // Create the delivery receipt
    if ($delivery->create()) {
        // Redirect to view page
        header("Location: view.php?id={$delivery->receipt_id}");
        exit();
    } else {
        $error_message = "Unable to create delivery receipt.";
    }
}

// Page title
$page_title = "Create Delivery Receipt for Quotation #" . $quotation_id;

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Create Delivery Receipt</h2>
            <p>For Quotation #<?php echo $quotation_id; ?> - <?php echo htmlspecialchars($quotation->customer_name); ?></p>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form method="post" id="deliveryForm">
                <!-- Delivery details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Delivery Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="delivery_date" class="form-label">Delivery Date</label>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="delivery_status" class="form-label">Delivery Status</label>
                                <select class="form-select" id="delivery_status" name="delivery_status" required>
                                    <option value="pending">Pending</option>
                                    <option value="in_transit">In Transit</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="recipient_name" class="form-label">Recipient Name</label>
                                <input type="text" class="form-control" id="recipient_name" name="recipient_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="recipient_position" class="form-label">Recipient Position</label>
                                <input type="text" class="form-control" id="recipient_position" name="recipient_position">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="delivery_address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="delivery_notes" class="form-label">Delivery Notes</label>
                            <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Items to be delivered -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Items to be Delivered</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item No</th>
                                        <th>Description</th>
                                        <th>Unit</th>
                                        <th>Ordered Qty</th>
                                        <th>Quantity to Deliver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($quotation->items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['item_no']; ?></td>
                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>
                                            <input type="hidden" name="item_id[]" value="<?php echo $item['item_id']; ?>">
                                            <input type="number" class="form-control quantity-delivered" name="quantity_delivered[]" value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $item['quantity']; ?>" step="0.01" required>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <a href="../quotation/view.php?id=<?php echo $quotation_id; ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-success">Create Delivery Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate that quantities to deliver are not greater than ordered quantities
    document.getElementById('deliveryForm').addEventListener('submit', function(e) {
        const inputs = document.querySelectorAll('.quantity-delivered');
        let valid = true;
        
        inputs.forEach(input => {
            const max = parseFloat(input.getAttribute('max'));
            const value = parseFloat(input.value);
            
            if (value > max) {
                alert('Quantity to deliver cannot be greater than ordered quantity.');
                input.focus();
                valid = false;
                e.preventDefault();
                return false;
            }
        });
        
        return valid;
    });
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>