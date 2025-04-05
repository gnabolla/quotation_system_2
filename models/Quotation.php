<?php
// File: models/Quotation.php
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/QuotationItem.php';
require_once __DIR__ . '/DocumentNumbering.php';

class Quotation {
    private $conn;
    private $table_name = "quotations";

    // Properties
    public $quotation_id;
    public $quotation_number;
    public $customer_name;
    public $customer_email;
    public $customer_phone;
    public $agency_name;
    public $agency_address;
    public $contact_person;
    public $quotation_date;
    public $valid_until;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;
    public $items = []; // Holds items for a single quotation instance

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $this->conn->beginTransaction();

            // Generate quotation number if not provided
            if (empty($this->quotation_number)) {
                $numbering = new DocumentNumbering($this->conn);
                $this->quotation_number = $numbering->generateQuotationNumber();
            }

            $query = "INSERT INTO " . $this->table_name . "
                    (quotation_number, customer_name, customer_email, customer_phone,
                     agency_name, agency_address, contact_person,
                     quotation_date, valid_until, status, notes)
                    VALUES
                    (:quotation_number, :customer_name, :customer_email, :customer_phone,
                     :agency_name, :agency_address, :contact_person,
                     :quotation_date, :valid_until, :status, :notes)";

            $stmt = $this->conn->prepare($query);

            // Sanitize
            $this->quotation_number = htmlspecialchars(strip_tags($this->quotation_number));
            $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
            $this->customer_email = htmlspecialchars(strip_tags($this->customer_email));
            $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
            $this->agency_name = htmlspecialchars(strip_tags($this->agency_name));
            $this->agency_address = htmlspecialchars(strip_tags($this->agency_address));
            $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->notes = htmlspecialchars(strip_tags($this->notes));

            // Bind values
            $stmt->bindParam(":quotation_number", $this->quotation_number);
            $stmt->bindParam(":customer_name", $this->customer_name);
            $stmt->bindParam(":customer_email", $this->customer_email);
            $stmt->bindParam(":customer_phone", $this->customer_phone);
            $stmt->bindParam(":agency_name", $this->agency_name);
            $stmt->bindParam(":agency_address", $this->agency_address);
            $stmt->bindParam(":contact_person", $this->contact_person);
            $stmt->bindParam(":quotation_date", $this->quotation_date);
            $stmt->bindParam(":valid_until", $this->valid_until);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":notes", $this->notes);

            $stmt->execute();
            $this->quotation_id = $this->conn->lastInsertId();

            // Insert items
            $item = new QuotationItem($this->conn);
            foreach ($this->items as $quotation_item) {
                $item->quotation_id = $this->quotation_id;
                $item->item_no = $quotation_item['item_no'];
                $item->quantity = $quotation_item['quantity'];
                $item->unit = $quotation_item['unit'];
                $item->description = $quotation_item['description'];
                $item->original_price = $quotation_item['original_price'];
                $item->markup_percentage = $quotation_item['markup_percentage'];
                $item->unit_price = $quotation_item['unit_price'];
                $item->total_amount = $quotation_item['total_amount'];
                if (!$item->create()) {
                    $this->conn->rollBack();
                    error_log("Failed to create quotation item: " . print_r($item, true));
                    return false;
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Quotation creation error: " . $e->getMessage());
            echo "Error: " . $e->getMessage(); // Keep for debugging if needed
            return false;
        }
    }

    // MODIFIED: readAll to include net income calculation and status filter
    public function readAll($statusFilter = null) {
        $query = "SELECT
                    q.quotation_id, q.quotation_number, q.customer_name, q.agency_name,
                    q.quotation_date, q.status, q.created_at,
                    COALESCE(SUM(qi.total_amount), 0) AS calculated_grand_total,
                    COALESCE(SUM(qi.original_price * qi.quantity), 0) AS calculated_original_cost,
                    (COALESCE(SUM(qi.total_amount), 0) - COALESCE(SUM(qi.original_price * qi.quantity), 0)) AS net_income
                  FROM
                    " . $this->table_name . " q
                  LEFT JOIN
                    quotation_items qi ON q.quotation_id = qi.quotation_id";

        // Add status filter if provided and not 'all'
        if ($statusFilter && $statusFilter !== 'all') {
            $query .= " WHERE q.status = :status";
        }

        $query .= " GROUP BY q.quotation_id
                    ORDER BY q.created_at DESC";

        $stmt = $this->conn->prepare($query);

        // Bind status filter if needed
        if ($statusFilter && $statusFilter !== 'all') {
            $stmt->bindParam(":status", $statusFilter);
        }

        $stmt->execute();
        return $stmt;
    }


    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ";

        if (isset($this->quotation_id) && !empty($this->quotation_id)) {
            $query .= "quotation_id = :id LIMIT 0,1";
            $param_name = ":id";
            $param_value = $this->quotation_id;
        } elseif (isset($this->quotation_number) && !empty($this->quotation_number)) {
            $query .= "quotation_number = :number LIMIT 0,1";
            $param_name = ":number";
            $param_value = $this->quotation_number;
        } else {
             error_log("readOne called without quotation_id or quotation_number.");
             return false; // Cannot proceed without an identifier
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam($param_name, $param_value);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->quotation_id = $row['quotation_id'];
            $this->quotation_number = $row['quotation_number'];
            $this->customer_name = $row['customer_name'];
            $this->customer_email = $row['customer_email'];
            $this->customer_phone = $row['customer_phone'];
            $this->agency_name = $row['agency_name'] ?? ''; // Use null coalescing operator
            $this->agency_address = $row['agency_address'] ?? '';
            $this->contact_person = $row['contact_person'] ?? '';
            $this->quotation_date = $row['quotation_date'];
            $this->valid_until = $row['valid_until'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            // Fetch associated items
            $item = new QuotationItem($this->conn);
            $item->quotation_id = $this->quotation_id;
            $this->items = $item->readByQuotationId();

            return true;
        }
        return false;
    }

    public function update() {
        try {
            $this->conn->beginTransaction();

            $query = "UPDATE " . $this->table_name . "
                    SET
                        customer_name = :customer_name,
                        customer_email = :customer_email,
                        customer_phone = :customer_phone,
                        agency_name = :agency_name,
                        agency_address = :agency_address,
                        contact_person = :contact_person,
                        quotation_date = :quotation_date,
                        valid_until = :valid_until,
                        status = :status,
                        notes = :notes
                    WHERE
                        quotation_id = :quotation_id";

            $stmt = $this->conn->prepare($query);

            // Sanitize
            $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
            $this->customer_email = htmlspecialchars(strip_tags($this->customer_email));
            $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
            $this->agency_name = htmlspecialchars(strip_tags($this->agency_name));
            $this->agency_address = htmlspecialchars(strip_tags($this->agency_address));
            $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->notes = htmlspecialchars(strip_tags($this->notes));
            $this->quotation_id = htmlspecialchars(strip_tags($this->quotation_id));

            // Bind values
            $stmt->bindParam(":customer_name", $this->customer_name);
            $stmt->bindParam(":customer_email", $this->customer_email);
            $stmt->bindParam(":customer_phone", $this->customer_phone);
            $stmt->bindParam(":agency_name", $this->agency_name);
            $stmt->bindParam(":agency_address", $this->agency_address);
            $stmt->bindParam(":contact_person", $this->contact_person);
            $stmt->bindParam(":quotation_date", $this->quotation_date);
            $stmt->bindParam(":valid_until", $this->valid_until);
            $stmt->bindParam(":status", $this->status);
            $stmt->bindParam(":notes", $this->notes);
            $stmt->bindParam(":quotation_id", $this->quotation_id);

            $stmt->execute();

            // Update items: Delete existing and re-insert
            $item = new QuotationItem($this->conn);
            $item->quotation_id = $this->quotation_id;
            if (!$item->deleteByQuotationId()) {
                 $this->conn->rollBack();
                 error_log("Failed to delete old items for quotation ID: " . $this->quotation_id);
                 return false;
            }


            foreach ($this->items as $quotation_item) {
                $item->quotation_id = $this->quotation_id; // Ensure correct ID is set
                $item->item_no = $quotation_item['item_no'];
                $item->quantity = $quotation_item['quantity'];
                $item->unit = $quotation_item['unit'];
                $item->description = $quotation_item['description'];
                $item->original_price = $quotation_item['original_price'];
                $item->markup_percentage = $quotation_item['markup_percentage'];
                $item->unit_price = $quotation_item['unit_price'];
                $item->total_amount = $quotation_item['total_amount'];
                if (!$item->create()) {
                    $this->conn->rollBack();
                     error_log("Failed to create updated quotation item: " . print_r($item, true));
                    return false;
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Quotation update error: " . $e->getMessage());
            echo "Error: " . $e->getMessage(); // Keep for debugging if needed
            return false;
        }
    }

    public function delete() {
        // Note: Related items should be deleted automatically due to ON DELETE CASCADE
        $query = "DELETE FROM " . $this->table_name . " WHERE quotation_id = :quotation_id";
        $stmt = $this->conn->prepare($query);

        $this->quotation_id = htmlspecialchars(strip_tags($this->quotation_id));
        $stmt->bindParam(":quotation_id", $this->quotation_id);

        if ($stmt->execute()) {
            return true;
        }
        error_log("Failed to delete quotation ID: " . $this->quotation_id);
        return false;
    }

    // Calculate Grand Total based on currently loaded items
    public function calculateGrandTotal() {
        $grand_total = 0;
        // Ensure items are loaded if called on a single instance without prior readOne
        if (empty($this->items) && $this->quotation_id) {
             $item = new QuotationItem($this->conn);
             $item->quotation_id = $this->quotation_id;
             $this->items = $item->readByQuotationId();
        }
        foreach ($this->items as $item) {
            $grand_total += floatval($item['total_amount']);
        }
        return $grand_total;
    }

     // NEW: Calculate Net Income based on currently loaded items
     public function calculateNetIncome() {
        $grandTotal = $this->calculateGrandTotal(); // Uses total_amount which includes markup
        $totalOriginalCost = 0;

         // Ensure items are loaded if called on a single instance without prior readOne
        if (empty($this->items) && $this->quotation_id) {
             $item = new QuotationItem($this->conn);
             $item->quotation_id = $this->quotation_id;
             $this->items = $item->readByQuotationId();
        }

        foreach ($this->items as $item) {
            // Ensure keys exist before accessing
            $original_price = isset($item['original_price']) ? floatval($item['original_price']) : 0;
            $quantity = isset($item['quantity']) ? floatval($item['quantity']) : 0;
            $totalOriginalCost += $original_price * $quantity;
        }
        return $grandTotal - $totalOriginalCost;
    }


    // Export functionality (remains largely the same, uses calculated totals)
    public function exportCSV() {
        // Ensure data is loaded if not already
        if (!isset($this->customer_name)) {
            if(!$this->readOne()){
                error_log("Failed to load quotation data for export (ID: {$this->quotation_id}).");
                return false; // Or handle error appropriately
            }
        }

        $filename = ($this->quotation_number ?? 'quotation_'.$this->quotation_id) . '_' . date('Y-m-d') . '.csv';

        // Output headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        if (!$output) {
             error_log("Failed to open php://output for CSV export.");
             return false;
        }

        // Header rows
        fputcsv($output, ['Quotation Number:', $this->quotation_number ?? 'QUO-'.str_pad($this->quotation_id, 3, '0', STR_PAD_LEFT)]);
        fputcsv($output, ['Customer:', $this->customer_name]);
        fputcsv($output, ['Agency:', $this->agency_name ?? 'N/A']);
        fputcsv($output, ['Date:', $this->quotation_date]);
        fputcsv($output, ['Valid Until:', $this->valid_until]);
        fputcsv($output, []); // Blank line

        // Item header
        fputcsv($output, ['Item No', 'Qty', 'Unit', 'Description', 'Original Price', 'Markup (%)', 'Unit Price', 'Total Amount']);

        // Item rows
        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                fputcsv($output, [
                    $item['item_no'],
                    $item['quantity'],
                    $item['unit'],
                    $item['description'],
                    number_format(floatval($item['original_price']), 2),
                    number_format(floatval($item['markup_percentage']), 2),
                    number_format(floatval($item['unit_price']), 2),
                    number_format(floatval($item['total_amount']), 2)
                ]);
            }
        } else {
             fputcsv($output, ['No items found for this quotation.']);
        }


        fputcsv($output, []); // Blank line
        // Totals
        $grandTotal = $this->calculateGrandTotal();
        $netIncome = $this->calculateNetIncome(); // Calculate net income based on loaded items
        fputcsv($output, ['', '', '', '', '', '', 'Grand Total:', number_format($grandTotal, 2)]);
        fputcsv($output, ['', '', '', '', '', '', 'Net Income:', number_format($netIncome, 2)]);


        fclose($output);
        return true;
    }
}
?>