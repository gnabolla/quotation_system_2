<?php
/**
 * Delete quotation
 * Path: quotation/delete.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/Quotation.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Quotation object
$quotation = new Quotation($db);

// Set ID of quotation to be deleted
$quotation->quotation_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Delete the quotation
if ($quotation->delete()) {
    // Redirect to quotation list with success message
    header("Location: list.php?action=deleted");
} else {
    // If unable to delete
    // Redirect to quotation list with error message
    header("Location: list.php?action=error");
}
?>