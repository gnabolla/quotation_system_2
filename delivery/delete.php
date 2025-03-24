<?php
/**
 * Delete delivery receipt
 * Path: delivery/delete.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/DeliveryReceipt.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize DeliveryReceipt object
$delivery = new DeliveryReceipt($db);

// Set ID of delivery receipt to be deleted
$delivery->receipt_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Read first to get quotation_id
$delivery->readOne();
$quotation_id = $delivery->quotation_id;

// Delete the delivery receipt
if ($delivery->delete()) {
    // Redirect to list page
    if (isset($_GET['redirect_to']) && $_GET['redirect_to'] == 'quotation') {
        header("Location: ../quotation/view.php?id={$quotation_id}");
    } else {
        header("Location: list.php" . ($quotation_id ? "?quotation_id={$quotation_id}" : ""));
    }
} else {
    // If unable to delete
    echo "Unable to delete the delivery receipt.";
}
?>