<?php
/**
 * Generate PDF for delivery receipt
 * Path: delivery/generate_pdf.php
 */

// Include TCPDF library
require_once '../vendor/autoload.php';

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/DeliveryReceipt.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize DeliveryReceipt object
$delivery = new DeliveryReceipt($db);

// Set ID of delivery receipt
if (isset($_GET['id'])) {
    $delivery->receipt_id = $_GET['id'];
} elseif (isset($_GET['number'])) {
    $delivery->receipt_number = $_GET['number'];
} else {
    die('ERROR: Missing ID or Number.');
}

// Read the details of delivery receipt
if (!$delivery->readOne()) {
    die('ERROR: Delivery receipt not found.');
}

// Create new PDF document
class MYPDF extends TCPDF
{
    // Page header
    public function Header()
    {
        // Logo
        $image_file = '../assets/images/company-logo.png';
        if (file_exists($image_file)) {
            // Adjust width/height of the logo if needed
            $this->Image($image_file, 15, 10, 25, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Company Name
        $this->SetFont('helvetica', 'B', 12);
        $this->SetXY(15, 10);
        $this->Cell(180, 6, 'TEKSTORE COMPUTER PARTS AND ACCESSORIES TRADING', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // Company Tagline
        $this->SetFont('helvetica', 'I', 9);
        $this->SetXY(15, 17);
        $this->Cell(180, 5, 'Fast and Quality Business Solution', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // Company Address
        $this->SetFont('helvetica', '', 8);
        $this->SetXY(15, 22);
        $this->Cell(180, 4, 'MAGSAYSAY ST., BANTUG, ROXAS, ISABELA', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // Contact Info
        $this->SetXY(15, 26);
        $this->Cell(180, 4, '09166027454 | tekstore.solution@gmail.com', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // Title
        $this->SetFont('helvetica', 'B', 12);
        $this->SetXY(15, 34);
        $this->Cell(180, 6, 'DELIVERY RECEIPT', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // Thin line separator
        $this->SetLineWidth(0.2);
        $this->Line(15, 42, 195, 42);
    }

    // Page footer
    public function Footer()
    {
        // Position at 10 mm from bottom
        $this->SetY(-10);
        // Set font
        $this->SetFont('helvetica', 'I', 7);
        // Page number
        $this->Cell(0, 7, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// Create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Quotation System');
$pdf->SetAuthor('TEKSTORE');
$pdf->SetTitle('Delivery Receipt ' . $delivery->receipt_number);
$pdf->SetSubject('Delivery Receipt');
$pdf->SetKeywords('Delivery, Receipt, Order');

// Remove default header/footer info to rely on custom
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Adjust margins (make them a bit smaller)
$pdf->SetMargins(10, 45, 10);   // left, top, right
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// If you want to force a single page and risk clipping, disable auto page break:
$pdf->SetAutoPageBreak(false, 10);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// MAIN FONT SIZE
$pdf->SetFont('helvetica', '', 9);

// ----------------- Receipt Info Section -------------------
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5, 'RECEIPT INFORMATION', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 8);

$html = '
<table cellspacing="0" cellpadding="4" border="0">
    <tr>
        <td width="50%" style="border:0.5px solid #cccccc; background-color:#f8f8f8;">
            <strong>RECEIPT DETAILS</strong><br>
            <table cellspacing="0" cellpadding="2" border="0">
                <tr>
                    <td width="40%"><strong>Receipt #:</strong></td>
                    <td width="60%">' . $delivery->receipt_number . '</td>
                </tr>
                <tr>
                    <td><strong>Date:</strong></td>
                    <td>' . date('m/d/Y', strtotime($delivery->delivery_date)) . '</td>
                </tr>
                <tr>
                    <td><strong>Quotation #:</strong></td>
                    <td>' . $delivery->quotation->quotation_number . '</td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>' . ucfirst(str_replace('_', ' ', $delivery->delivery_status)) . '</td>
                </tr>
            </table>
        </td>
        <td width="50%" style="border:0.5px solid #cccccc; background-color:#f8f8f8;">
            <strong>CLIENT INFORMATION</strong><br>
            <table cellspacing="0" cellpadding="2" border="0">
                <tr>
                    <td width="30%"><strong>Name:</strong></td>
                    <td width="70%">' . htmlspecialchars($delivery->quotation->customer_name) . '</td>
                </tr>
                <tr>
                    <td><strong>Address:</strong></td>
                    <td>' . nl2br(htmlspecialchars($delivery->delivery_address)) . '</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>';

$pdf->writeHTML($html, true, false, true, false, '');


// ----------------- Items Section -------------------
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 5, 'DELIVERED ITEMS', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 8);

// Build items table with matching column widths in <th> and <td>
$items_html = '
<table border="0.5" cellpadding="4" cellspacing="0" width="100%">
    <thead>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <th width="5%"  align="center">No.</th>
            <th width="35%" align="left">Description</th>
            <th width="10%" align="center">Unit</th>
            <th width="10%" align="right">Qty</th>
            <th width="15%" align="right">Unit Price</th>
            <th width="15%" align="right">Total</th>
        </tr>
    </thead>
    <tbody>';

$grand_total = 0;
if (!empty($delivery->items)) {
    $counter = 1;
    foreach ($delivery->items as $item) {
        // Look up unit_price from quotation items
        $unit_price = 0;
        $total      = 0;
        foreach ($delivery->quotation->items as $q_item) {
            if ($q_item['item_id'] == $item['item_id']) {
                $unit_price = $q_item['unit_price'];
                $total      = $unit_price * $item['quantity_delivered'];
                $grand_total += $total;
                break;
            }
        }

        // Optional row shading
        $bgcolor = ($counter % 2 == 0) ? 'background-color: #f9f9f9;' : '';

        $items_html .= '
        <tr style="' . $bgcolor . '">
            <td width="5%"  align="center">' . $counter++ . '</td>
            <td width="35%" align="left">' . htmlspecialchars($item['description']) . '</td>
            <td width="10%" align="center">' . htmlspecialchars($item['unit']) . '</td>
            <!-- Remove decimal from quantity: number_format with 0 decimals -->
            <td width="10%" align="right">' . number_format($item['quantity_delivered'], 0) . '</td>
            <!-- Add "P" for Peso sign in front of prices -->
            <td width="15%" align="right">P ' . number_format($unit_price, 2) . '</td>
            <td width="15%" align="right">P ' . number_format($total, 2) . '</td>
        </tr>';
    }

    // Grand Total row (keep columns + widths consistent)
    $items_html .= '
    <tr style="background-color: #e6e6e6; font-weight: bold;">
        <td width="5%"></td>
        <td width="35%"></td>
        <td width="10%"></td>
        <td width="10%"></td>
        <td width="15%" align="right">Grand Total:</td>
        <td width="15%" align="right">P ' . number_format($grand_total, 2) . '</td>
    </tr>';
} else {
    // If no items
    $items_html .= '
    <tr>
        <td colspan="6" align="center">No items found</td>
    </tr>';
}

$items_html .= '
    </tbody>
</table>';

$pdf->writeHTML($items_html, true, false, true, false, '');

// ----------------- Signature Section -------------------
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 8);

$signature_html = '
<table cellpadding="4" border="0" width="100%">
    <tr>
        <td width="45%"><strong>Received by:</strong></td>
        <td width="10%"></td>
        <td width="45%"><strong>Delivered by:</strong></td>
    </tr>
    <tr>
        <td height="30" style="border-bottom: 0.5px solid #000;">';

// If there's a recipient signature in base64
if (!empty($delivery->recipient_signature)) {
    $sig_data = explode(',', $delivery->recipient_signature);
    if (count($sig_data) > 1) {
        $signature_html .= '<img src="' . $delivery->recipient_signature . '" height="30" />';
    }
}

$signature_html .= '
        </td>
        <td></td>
        <td height="30" style="border-bottom: 0.5px solid #000;"></td>
    </tr>
    <tr>
        <td align="center">' . htmlspecialchars($delivery->recipient_name) . '</td>
        <td></td>
        <td align="center">Owner/Representative</td>
    </tr>
    <tr>
        <td align="center">' . htmlspecialchars($delivery->recipient_position) . '</td>
        <td></td>
    </tr>
    <tr>
        <td align="center">Date: ' . date('m/d/Y', strtotime($delivery->delivery_date)) . '</td>
        <td></td>
        <td align="center">Date: ' . date('m/d/Y', strtotime($delivery->delivery_date)) . '</td>
    </tr>
</table>';

$pdf->writeHTML($signature_html, true, false, true, false, '');

// ----------------- Output the PDF -------------------
$pdf_filename = 'delivery_receipt_' . $delivery->receipt_number . '.pdf';
$pdf->Output($pdf_filename, 'I'); // 'I' sends to browser
