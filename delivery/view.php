<?php
/**
 * View delivery receipt
 * Path: delivery/view.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/DeliveryReceipt.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize DeliveryReceipt object
$delivery = new DeliveryReceipt($db);

// Set ID of delivery receipt to be viewed
$delivery->receipt_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Read the details of delivery receipt
if (!$delivery->readOne()) {
    // Redirect to list page if ID doesn't exist
    header("Location: ../quotation/list.php");
    exit();
}

// Handle signature submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signature_data'])) {
    $signature_data = $_POST['signature_data'];
    if ($delivery->saveSignature($signature_data)) {
        // Refresh the page to show the signature
        header("Location: view.php?id={$delivery->receipt_id}&signature=saved");
        exit();
    }
}

// Page title
$page_title = "Delivery Receipt #" . $delivery->receipt_id;

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <!-- Action buttons -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Delivery Receipt #<?php echo $delivery->receipt_id; ?></h2>
                <div>
                    <a href="../quotation/view.php?id=<?php echo $delivery->quotation_id; ?>" class="btn btn-secondary">Back to Quotation</a>
                    <a href="edit.php?id=<?php echo $delivery->receipt_id; ?>" class="btn btn-warning">Edit</a>
                    <a href="generate_pdf.php?id=<?php echo $delivery->receipt_id; ?>" class="btn btn-primary" target="_blank">Generate PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery details -->
    <div class="card mb-3">
        <div class="card-header">
            <h4 class="m-0">Delivery Information</h4>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6 mb-1">
                    <strong>Customer:</strong> <?php echo htmlspecialchars($delivery->quotation->customer_name); ?>
                </div>
                <div class="col-md-6 mb-1">
                    <strong>Delivery Date:</strong> <?php echo date('m/d/Y', strtotime($delivery->delivery_date)); ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6 mb-1">
                    <strong>Recipient:</strong> <?php echo htmlspecialchars($delivery->recipient_name); ?>
                    <?php if(!empty($delivery->recipient_position)): ?>
                    (<?php echo htmlspecialchars($delivery->recipient_position); ?>)
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-1">
                    <strong>Status:</strong> 
                    <span class="badge <?php 
                        echo ($delivery->delivery_status == 'pending') ? 'bg-warning' : 
                            (($delivery->delivery_status == 'in_transit') ? 'bg-primary' : 
                                (($delivery->delivery_status == 'delivered') ? 'bg-success' : 'bg-danger')); 
                    ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $delivery->delivery_status)); ?>
                    </span>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-12 mb-1">
                    <strong>Delivery Address:</strong>
                    <?php echo nl2br(htmlspecialchars($delivery->delivery_address)); ?>
                </div>
            </div>
            <?php if(!empty($delivery->delivery_notes)): ?>
            <div class="row">
                <div class="col-md-12 mb-1">
                    <strong>Notes:</strong>
                    <?php echo nl2br(htmlspecialchars($delivery->delivery_notes)); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delivery items -->
    <div class="card mb-3">
        <div class="card-header">
            <h4 class="m-0">Items Delivered</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered m-0">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="35%">Description</th>
                            <th width="8%">Unit</th>
                            <th width="10%">Quantity</th>
                            <th width="12%">Unit Price</th>
                            <th width="15%">Total</th>
                            <th width="15%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        if (!empty($delivery->items)): 
                            $counter = 1; 
                            foreach($delivery->items as $item): 
                                // Look up the item in quotation items to get unit price
                                $unit_price = 0;
                                $total = 0;
                                foreach($delivery->quotation->items as $q_item) {
                                    if ($q_item['item_id'] == $item['item_id']) {
                                        $unit_price = $q_item['unit_price'];
                                        $total = $unit_price * $item['quantity_delivered'];
                                        $grand_total += $total;
                                        break;
                                    }
                                }
                        ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($item['description']); ?></td>
                                <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                <td><?php echo number_format((int)$item['quantity_delivered']); ?></td>
                                <td class="text-end"><?php echo number_format($unit_price, 2); ?></td>
                                <td class="text-end"><?php echo number_format($total, 2); ?></td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                            <!-- Grand Total row -->
                            <tr>
                                <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                                <td class="text-end"><strong><?php echo number_format($grand_total, 2); ?></strong></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Signature section -->
    <div class="card mb-3">
        <div class="card-header">
            <h4 class="m-0">Recipient Signature</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($delivery->recipient_signature)): ?>
                <div class="text-center mb-3">
                    <img src="<?php echo $delivery->recipient_signature; ?>" alt="Recipient Signature" class="img-fluid" style="max-height: 150px;">
                    <p class="mt-2">Signed by: <?php echo htmlspecialchars($delivery->recipient_name); ?></p>
                </div>
            <?php else: ?>
                <?php if ($delivery->delivery_status == 'delivered'): ?>
                    <div class="alert alert-warning">No signature has been recorded for this delivery.</div>
                    
                    <div class="mb-3">
                        <p>Please sign below:</p>
                        <canvas id="signatureCanvas" width="600" height="200" style="border: 1px solid #ddd; width: 100%; max-width: 600px;"></canvas>
                    </div>
                    
                    <div class="mb-3">
                        <form id="signatureForm" method="post">
                            <input type="hidden" name="signature_data" id="signatureData">
                            <button type="button" id="clearSignature" class="btn btn-secondary">Clear</button>
                            <button type="submit" class="btn btn-primary">Save Signature</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Signature can only be added when delivery status is "Delivered".
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for signature pad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if canvas exists
    const canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    
    // Set canvas drawing style
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#000';
    
    // Start drawing
    canvas.addEventListener('mousedown', function(e) {
        isDrawing = true;
        [lastX, lastY] = [e.offsetX, e.offsetY];
    });
    
    // Draw
    canvas.addEventListener('mousemove', function(e) {
        if (!isDrawing) return;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        [lastX, lastY] = [e.offsetX, e.offsetY];
    });
    
    // Stop drawing
    canvas.addEventListener('mouseup', function() {
        isDrawing = false;
    });
    
    canvas.addEventListener('mouseout', function() {
        isDrawing = false;
    });
    
    // Handle touch events for mobile
    canvas.addEventListener('touchstart', function(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const rect = canvas.getBoundingClientRect();
        const offsetX = touch.clientX - rect.left;
        const offsetY = touch.clientY - rect.top;
        
        isDrawing = true;
        [lastX, lastY] = [offsetX, offsetY];
    });
    
    canvas.addEventListener('touchmove', function(e) {
        e.preventDefault();
        if (!isDrawing) return;
        
        const touch = e.touches[0];
        const rect = canvas.getBoundingClientRect();
        const offsetX = touch.clientX - rect.left;
        const offsetY = touch.clientY - rect.top;
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(offsetX, offsetY);
        ctx.stroke();
        [lastX, lastY] = [offsetX, offsetY];
    });
    
    canvas.addEventListener('touchend', function(e) {
        e.preventDefault();
        isDrawing = false;
    });
    
    // Clear signature
    document.getElementById('clearSignature').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    });
    
    // Save signature
    document.getElementById('signatureForm').addEventListener('submit', function(e) {
        // Convert canvas to base64 data URL
        const signatureData = canvas.toDataURL('image/png');
        
        // Check if signature is empty
        const emptyCanvas = document.createElement('canvas');
        emptyCanvas.width = canvas.width;
        emptyCanvas.height = canvas.height;
        
        if (signatureData === emptyCanvas.toDataURL('image/png')) {
            e.preventDefault();
            alert('Please provide a signature before saving.');
            return false;
        }
        
        // Set the data URL as form value
        document.getElementById('signatureData').value = signatureData;
    });
});
</script>

<?php
// Include footer
include_once '../includes/footer.php';
?>