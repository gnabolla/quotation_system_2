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

// Set ID or number of delivery receipt to be viewed
if (isset($_GET['id'])) {
    $delivery->receipt_id = $_GET['id'];
} elseif (isset($_GET['number'])) {
    $delivery->receipt_number = $_GET['number'];
} else {
    die('ERROR: Missing ID or Number.');
}

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
$page_title = "Delivery Receipt " . $delivery->receipt_number;

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <!-- Print only section for printing -->
    <div class="d-none d-print-block">
        <div class="delivery-receipt-header">
            <h1>DELIVERY RECEIPT</h1>
            <h2>Tekstore Computer Parts and Accessories Trading</h2>
            <p>Fast and Quality Business Solution</p>
            <div class="company-contact-info">
                <p>Magsaysay Street, Bantug, Roxas, Isabela | 09166027454 | tekstore.solution@gmail.com</p>
            </div>
        </div>
    </div>

    <!-- Action buttons (hidden when printing) -->
    <div class="row mb-3 d-print-none">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Delivery Receipt <?php echo $delivery->receipt_number; ?></h2>
                <div>
                    <a href="../quotation/view.php?id=<?php echo $delivery->quotation_id; ?>" class="btn btn-secondary">Back to Quotation</a>
                    <a href="edit.php?id=<?php echo $delivery->receipt_id; ?>" class="btn btn-warning">Edit</a>
                    <button onclick="window.print();" class="btn btn-info">Print</button>
                    <a href="generate_pdf.php?id=<?php echo $delivery->receipt_id; ?>" class="btn btn-primary" target="_blank">Generate PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Information (visible in both screen and print modes) -->
    <div class="card mb-3">
        <div class="card-header">
            <h4 class="m-0">RECEIPT INFORMATION</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Receipt Details</h5>
                    <div class="mb-2">
                        <strong>Receipt Number:</strong> <?php echo $delivery->receipt_number; ?>
                    </div>
                    <div class="mb-2">
                        <strong>Date:</strong> <?php echo date('F j, Y', strtotime($delivery->delivery_date)); ?>
                    </div>
                    <div class="mb-2">
                        <strong>Quotation #:</strong> <?php echo $delivery->quotation->quotation_number; ?>
                    </div>
                    <div class="mb-2">
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
                <div class="col-md-6">
                    <h5>Client Information</h5>
                    <div class="mb-2">
                        <strong>Name:</strong> <?php echo htmlspecialchars($delivery->quotation->customer_name); ?>
                    </div>
                    <div class="mb-2">
                        <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($delivery->delivery_address)); ?>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-6">
                    <h5>Agency Information</h5>
                    <div class="mb-2">
                        <strong>Agency:</strong> <?php echo htmlspecialchars($delivery->quotation->agency_name ?? 'N/A'); ?>
                    </div>
                    <div class="mb-2">
                        <strong>Address:</strong> <?php echo htmlspecialchars($delivery->quotation->agency_address ?? 'N/A'); ?>
                    </div>
                    <div class="mb-2">
                        <strong>Contact Person:</strong> <?php echo htmlspecialchars($delivery->quotation->contact_person ?? 'N/A'); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Delivery Information</h5>
                    <div class="mb-2">
                        <strong>Recipient:</strong> <?php echo htmlspecialchars($delivery->recipient_name); ?>
                        <?php if(!empty($delivery->recipient_position)): ?>
                        (<?php echo htmlspecialchars($delivery->recipient_position); ?>)
                        <?php endif; ?>
                    </div>
                    <?php if(!empty($delivery->delivery_notes)): ?>
                    <div class="mb-2">
                        <strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($delivery->delivery_notes)); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delivery items -->
    <div class="card mb-3">
        <div class="card-header">
            <h4 class="m-0">DELIVERED ITEMS</h4>
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
                                <td><?php echo number_format((float)$item['quantity_delivered'], 2); ?></td>
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
                    <div class="alert alert-warning d-print-none">No signature has been recorded for this delivery.</div>
                    
                    <div class="mb-3 d-print-none">
                        <p>Please sign below:</p>
                        <canvas id="signatureCanvas" width="600" height="200" style="border: 1px solid #ddd; width: 100%; max-width: 600px;"></canvas>
                    </div>
                    
                    <div class="mb-3 d-print-none">
                        <form id="signatureForm" method="post">
                            <input type="hidden" name="signature_data" id="signatureData">
                            <button type="button" id="clearSignature" class="btn btn-secondary">Clear</button>
                            <button type="submit" class="btn btn-primary">Save Signature</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info d-print-none">
                        Signature can only be added when delivery status is "Delivered".
                    </div>
                <?php endif; ?>
                
                <!-- Print version of signature space -->
                <div class="d-none d-print-block">
                    <div class="row confirmation-section">
                        <div class="col-md-6 text-center">
                            <div class="signature-space"></div>
                            <p class="signature-name"><?php echo htmlspecialchars($delivery->recipient_name); ?></p>
                            <p class="signature-position"><?php echo htmlspecialchars($delivery->recipient_position); ?></p>
                            <p class="signature-date">Date: <?php echo date('m/d/Y', strtotime($delivery->delivery_date)); ?></p>
                        </div>
                        <div class="col-md-6 text-center">
                            <div class="signature-space"></div>
                            <p class="signature-name">MARI CHRIS B. MAGUSIB</p>
                            <p class="signature-position">Contact Person</p>
                            <p class="signature-date">Date: <?php echo date('m/d/Y', strtotime($delivery->delivery_date)); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for signature pad (only shown in non-print mode) -->
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