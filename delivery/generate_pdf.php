<?php
/**
 * Generate PDF for delivery receipt
 * Path: delivery/generate_pdf.php
 */

// Include TCPDF library (update this path to where you installed TCPDF)
require_once '../libs/tcpdf/tcpdf.php';

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/DeliveryReceipt.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize DeliveryReceipt object
$delivery = new DeliveryReceipt($db);

// Set ID of delivery receipt
$delivery->receipt_id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

// Read the details of delivery receipt
if (!$delivery->readOne()) {
    die('ERROR: Delivery receipt not found.');
}

// Create new PDF document
class MYPDF extends TCPDF {
    // Page header
    public function Header() {
        // Logo
        $image_file = '../assets/images/company-logo.png';
        if (file_exists($image_file)) {
            $this->Image($image_file, 15, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        
        // Set font
        $this->SetFont('helvetica', 'B', 16);
        
        // Company Name
        $this->SetXY(15, 10);
        $this->Cell(180, 10, 'TEKSTORE COMPUTER PARTS AND ACCESSORIES TRADING', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Company Address
        $this->SetFont('helvetica', '', 10);
        $this->SetXY(15, 18);
        $this->Cell(180, 6, 'MAGSAYSAY ST., BANTUG, ROXAS, ISABELA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // TIN Number
        $this->SetXY(15, 24);
        $this->Cell(180, 6, 'TIN#: 316-318-194-00000', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Contact Info
        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY(15, 32);
        $this->Cell(180, 6, 'CONTACT INFO:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        $this->SetFont('helvetica', '', 10);
        $this->SetXY(15, 38);
        $this->Cell(180, 6, 'FERICK JOHN B. RAGOJOS - BUSINESS OWNER - 09166027454', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Title
        $this->SetFont('helvetica', 'B', 16);
        $this->SetXY(15, 48);
        $this->Cell(180, 10, 'DELIVERY RECEIPT', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Line
        $this->Line(15, 60, 195, 60);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Quotation System');
$pdf->SetAuthor('TEKSTORE');
$pdf->SetTitle('Delivery Receipt #' . $delivery->receipt_id);
$pdf->SetSubject('Delivery Receipt');
$pdf->SetKeywords('Delivery, Receipt, Order');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(15, 65, 15);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 25);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 11);

// Delivery Info Section
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(180, 10, 'Delivery Information', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);

// Customer and Delivery Details
$html = '<table cellspacing="0" cellpadding="2" border="0">
    <tr>
        <td width="25%"><strong>Delivery Receipt #:</strong></td>
        <td width="25%">' . $delivery->receipt_id . '</td>
        <td width="25%"><strong>Quotation #:</strong></td>
        <td width="25%">' . $delivery->quotation_id . '</td>
    </tr>
    <tr>
        <td><strong>Customer:</strong></td>
        <td>' . htmlspecialchars($delivery->quotation->customer_name) . '</td>
        <td><strong>Delivery Date:</strong></td>
        <td>' . date('m/d/Y', strtotime($delivery->delivery_date)) . '</td>
    </tr>
    <tr>
        <td><strong>Recipient:</strong></td>
        <td>' . htmlspecialchars($delivery->recipient_name);
        
if(!empty($delivery->recipient_position)) {
    $html .= ' (' . htmlspecialchars($delivery->recipient_position) . ')';
}

$html .= '</td>
        <td><strong>Status:</strong></td>
        <td>' . ucfirst(str_replace('_', ' ', $delivery->delivery_status)) . '</td>
    </tr>
    <tr>
        <td><strong>Delivery Address:</strong></td>
        <td colspan="3">' . nl2br(htmlspecialchars($delivery->delivery_address)) . '</td>
    </tr>';

if(!empty($delivery->delivery_notes)) {
    $html .= '<tr>
        <td><strong>Notes:</strong></td>
        <td colspan="3">' . nl2br(htmlspecialchars($delivery->delivery_notes)) . '</td>
    </tr>';
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Items Section
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(180, 10, 'Items Delivered', 0, 1, 'L');

// Instead of drawing the table cell by cell, we'll use HTML for better wrapping of long text
$pdf->SetFont('helvetica', '', 9); // Slightly smaller font for the items table

// Start the HTML for the items table
$items_html = '<table border="1" cellpadding="5">
    <thead>
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <th width="8%" align="center">No.</th>
            <th width="37%" align="center">Description</th>
            <th width="10%" align="center">Unit</th>
            <th width="10%" align="center">Quantity</th>
            <th width="15%" align="center">Unit Price</th>
            <th width="20%" align="center">Total</th>
        </tr>
    </thead>
    <tbody>';

// Table Content
$grand_total = 0;

if (!empty($delivery->items)) {
    $counter = 1;
    foreach($delivery->items as $item) {
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
        
        $items_html .= '<tr>
            <td align="center">' . $counter++ . '</td>
            <td>' . htmlspecialchars($item['description']) . '</td>
            <td align="center">' . htmlspecialchars($item['unit']) . '</td>
            <td align="right">' . number_format((float)$item['quantity_delivered'], 2) . '</td>
            <td align="right">' . number_format($unit_price, 2) . '</td>
            <td align="right">' . number_format($total, 2) . '</td>
        </tr>';
    }
    
    // Add empty rows to ensure consistent appearance (optional, you can remove if not needed)
    for($i = count($delivery->items); $i < 5; $i++) {
        $items_html .= '<tr>
            <td align="center">' . ($i + 1) . '</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>';
    }
    
    // Grand Total row
    $items_html .= '<tr>
        <td colspan="5" align="right"><strong>Grand Total:</strong></td>
        <td align="right"><strong>' . number_format($grand_total, 2) . '</strong></td>
    </tr>';
} else {
    // If no items, show placeholder message
    $items_html .= '<tr>
        <td colspan="6" align="center">No items found</td>
    </tr>';
}

$items_html .= '</tbody></table>';

// Output the items table
$pdf->writeHTML($items_html, true, false, true, false, '');

// Signature Section
$pdf->Ln(15);
$pdf->SetFont('helvetica', '', 10);

// Using HTML for signature area for better control
$signature_html = '<table cellpadding="5" border="0">
    <tr>
        <td width="45%"><strong>Received by:</strong></td>
        <td width="10%">&nbsp;</td>
        <td width="45%"><strong>Delivered by:</strong></td>
    </tr>
    <tr>
        <td height="40px" style="border-bottom: 1px solid #000000;">';

// Add recipient signature if available
if (!empty($delivery->recipient_signature)) {
    $signature_img = $delivery->recipient_signature;
    // Extract the base64 encoded image data
    $sig_data = explode(',', $signature_img);
    if (count($sig_data) > 1) {
        $signature_html .= '<img src="' . $signature_img . '" height="40" />';
    }
}

$signature_html .= '</td>
        <td>&nbsp;</td>
        <td height="40px" style="border-bottom: 1px solid #000000;">&nbsp;</td>
    </tr>
    <tr>
        <td align="center">' . htmlspecialchars($delivery->recipient_name) . '</td>
        <td>&nbsp;</td>
        <td align="center">FERICK JOHN B. RAGOJOS</td>
    </tr>
    <tr>
        <td align="center">' . htmlspecialchars($delivery->recipient_position) . '</td>
        <td>&nbsp;</td>
        <td align="center">Business Owner</td>
    </tr>
    <tr>
        <td align="center">Date: ' . date('m/d/Y', strtotime($delivery->delivery_date)) . '</td>
        <td>&nbsp;</td>
        <td align="center">Date: ' . date('m/d/Y', strtotime($delivery->delivery_date)) . '</td>
    </tr>
</table>';

$pdf->writeHTML($signature_html, true, false, true, false, '');

// Output the PDF
$pdf_filename = 'delivery_receipt_' . $delivery->receipt_id . '.pdf';
$pdf->Output($pdf_filename, 'I'); // 'I' means send to browser