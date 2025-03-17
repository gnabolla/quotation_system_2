<?php
/**
 * Export quotation data in different formats
 * Path: quotation/export.php
 */

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/Quotation.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Quotation object
$quotation = new Quotation($db);

// Set ID of quotation to be exported
$quotation->quotation_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Get export type
$export_type = isset($_GET['type']) ? strtolower($_GET['type']) : 'csv';

// Read the details of quotation
if (!$quotation->readOne()) {
    // Redirect to list page if ID doesn't exist
    header("Location: list.php");
    exit();
}

// Export based on type
switch ($export_type) {
    case 'csv':
        exportCSV($quotation);
        break;
    case 'excel':
        exportExcel($quotation);
        break;
    case 'pdf':
        exportPDF($quotation);
        break;
    default:
        // Default to CSV
        exportCSV($quotation);
}

// Export as CSV
function exportCSV($quotation) {
    // File name
    $filename = 'quotation_' . $quotation->quotation_id . '_' . date('Y-m-d') . '.csv';
    
    // Set headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add quotation header information
    fputcsv($output, ['Quotation #' . $quotation->quotation_id]);
    fputcsv($output, ['Customer:', $quotation->customer_name]);
    fputcsv($output, ['Date:', $quotation->quotation_date]);
    fputcsv($output, ['Valid Until:', $quotation->valid_until]);
    fputcsv($output, []);  // Empty row for spacing
    
    // Add column headers
    fputcsv($output, ['Item No', 'Qty', 'Unit', 'Description', 'Original Price', 'Markup (%)', 'Unit Price', 'Total Amount']);
    
    // Add data rows
    foreach ($quotation->items as $item) {
        fputcsv($output, [
            $item['item_no'],
            $item['quantity'],
            $item['unit'],
            $item['description'],
            $item['original_price'],
            $item['markup_percentage'],
            $item['unit_price'],
            $item['total_amount']
        ]);
    }
    
    // Add grand total row
    fputcsv($output, []);  // Empty row for spacing
    fputcsv($output, ['', '', '', '', '', '', 'Grand Total:', $quotation->calculateGrandTotal()]);
    
    // Close output stream
    fclose($output);
    exit;
}

// Export as Excel (requires PhpSpreadsheet library)
function exportExcel($quotation) {
    // Check if PhpSpreadsheet is installed
    if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
        echo "PhpSpreadsheet library is not installed. Please install it using Composer:";
        echo "<pre>composer require phpoffice/phpspreadsheet</pre>";
        echo "<p>For demo purposes, we'll export as CSV instead.</p>";
        echo "<p><a href='export.php?id={$quotation->quotation_id}&type=csv'>Download CSV</a></p>";
        exit;
    }
    
    // For demonstration purposes only
    // In a real implementation, you would use PhpSpreadsheet to create Excel files
    header("Location: export.php?id={$quotation->quotation_id}&type=csv");
    exit;
}

// Export as PDF (requires library like TCPDF, FPDF, or mPDF)
function exportPDF($quotation) {
    // Check if any PDF library is installed
    if (!class_exists('TCPDF') && !class_exists('FPDF') && !class_exists('Mpdf\Mpdf')) {
        echo "No PDF library is installed. Please install one of the following:";
        echo "<pre>composer require tecnickcom/tcpdf</pre>";
        echo "<pre>composer require setasign/fpdf</pre>";
        echo "<pre>composer require mpdf/mpdf</pre>";
        echo "<p>For demo purposes, we'll export as CSV instead.</p>";
        echo "<p><a href='export.php?id={$quotation->quotation_id}&type=csv'>Download CSV</a></p>";
        exit;
    }
    
    // For demonstration purposes only
    // In a real implementation, you would use a PDF library to create PDF files
    header("Location: export.php?id={$quotation->quotation_id}&type=csv");
    exit;
}