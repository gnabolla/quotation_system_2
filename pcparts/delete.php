<?php
/**
 * Delete PC parts quotation
 * Path: pcparts/delete.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/PCPartsQuotation.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize PCPartsQuotation object
$quotation = new PCPartsQuotation($db);

// Set ID of quotation to be deleted
$quotation->quotation_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Delete the quotation
if ($quotation->delete()) {
    // Redirect to quotation list
    header("Location: list.php?action=deleted");
    exit();
} else {
    // If unable to delete
    echo "Unable to delete PC parts quotation.";
}