<?php
/**
 * PC Builder quotation creation page
 * Path: pcbuilder/quotation.php
 */

// Start session
session_start();

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/PCBuilder.php';
require_once '../models/Quotation.php';
require_once '../models/QuotationItem.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize PCBuilder object
$pcBuilder = new PCBuilder($db);

// Load configuration from session if exists
$pcBuilder->loadFromSession();

// Check if there's anything in the configuration
$has_items = !empty($pcBuilder->configuration['bundle']) || 
             !empty($pcBuilder->configuration['items']) || 
             !empty($pcBuilder->configuration['custom_items']);

// Calculate current total
$total_price = $pcBuilder->calculateTotalPrice();

// Process form submission
$error_message = null;
$success_message = null;
$quotation_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && $has_items) {
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $markup_percentage = isset($_POST['markup_percentage']) ? floatval($_POST['markup_percentage']) : 0;
    
    if (empty($customer_name)) {
        $error_message = "Customer name is required.";
    } else {
        // Initialize Quotation object
        $quotation = new Quotation($db);
        
        // Generate quotation data from PC Builder configuration
        $quotation_data = $pcBuilder->exportForQuotation($customer_name, $markup_percentage);
        
        // Set quotation properties
        $quotation->customer_name = $customer_name;
        $quotation->customer_email = $customer_email;
        $quotation->customer_phone = $customer_phone;
        $quotation->quotation_date = $quotation_data['quotation_date'];
        $quotation->valid_until = $quotation_data['valid_until'];
        $quotation->status = $quotation_data['status'];
        $quotation->items = $quotation_data['items'];
        
        // Create the quotation
        if ($quotation->create()) {
            $quotation_id = $quotation->quotation_id;
            $success_message = "Quotation created successfully!";
            
            // Optionally reset the PC Builder configuration
            if (isset($_POST['reset_after'])) {
                $pcBuilder->resetConfiguration();
                $pcBuilder->saveToSession();
            }
        } else {
            $error_message = "Failed to create quotation. Please try again.";
        }
    }
}

// Page title
$page_title = "Create Quotation from PC Build";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Create Quotation from PC Build</h2>
                <a href="index.php" class="btn btn-secondary">Back to Builder</a>
            </div>
        </div>
    </div>

    <?php if ($success_message): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-success">
                <?php echo $success_message; ?>
                <?php if ($quotation_id): ?>
                <div class="mt-3">
                    <a href="../quotation/view.php?id=<?php echo $quotation_id; ?>" class="btn btn-primary">View Quotation</a>
                    <a href="index.php" class="btn btn-secondary">Return to PC Builder</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$has_items): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                <h4>Your PC Build is empty!</h4>
                <p>Please add at least one item to your configuration before creating a quotation.</p>
                <a href="index.php" class="btn btn-primary">Go to PC Builder</a>
            </div>
        </div>
    </div>
    <?php elseif (!$success_message): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4>Your PC Configuration Summary</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($pcBuilder->configuration['bundle'])): 
                                    $bundle = $pcBuilder->configuration['bundle'];
                                ?>
                                <tr>
                                    <td>Bundle</td>
                                    <td><?php echo $bundle['bundle_type'] . ': ' . $bundle['processor'] . ' + ' . $bundle['motherboard']; ?></td>
                                    <td>₱<?php echo number_format($bundle['price'], 2); ?></td>
                                    <td>1</td>
                                    <td>₱<?php echo number_format($bundle['price'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($pcBuilder->configuration['items'])): 
                                    foreach ($pcBuilder->configuration['items'] as $item):
                                        $subtotal = $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item['category_name']; ?></td>
                                    <td><?php echo $item['item_name']; ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                                <?php endforeach;
                                endif; ?>
                                
                                <?php if (!empty($pcBuilder->configuration['custom_items'])): 
                                    foreach ($pcBuilder->configuration['custom_items'] as $item):
                                        $subtotal = $item['price'] * $item['quantity'];
                                ?>
                                <tr>
                                    <td><?php echo $item['category_name']; ?> (Custom)</td>
                                    <td><?php echo $item['item_name']; ?></td>
                                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                                <?php endforeach;
                                endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-dark">
                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                    <td class="fw-bold">₱<?php echo number_format($total_price, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-3 text-end">
                        <a href="index.php" class="btn btn-info">Edit Configuration</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4>Create Quotation</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="quotation.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label">Customer Name*</label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="customer_email" class="form-label">Customer Email</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customer_phone" class="form-label">Customer Phone</label>
                                <input type="text" class="form-control" id="customer_phone" name="customer_phone">
                            </div>
                            <div class="col-md-6">
                                <label for="markup_percentage" class="form-label">Markup Percentage (%)</label>
                                <input type="number" class="form-control" id="markup_percentage" name="markup_percentage" value="0" min="0" step="0.01">
                                <div class="form-text">Add a markup percentage to the original prices.</div>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="reset_after" name="reset_after" value="1">
                            <label class="form-check-label" for="reset_after">Reset PC Builder after creating quotation</label>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-success">Create Quotation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>