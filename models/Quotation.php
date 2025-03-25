<?php
/**
 * Quotation model class for CRUD operations
 * Path: models/Quotation.php
 */

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/QuotationItem.php';
require_once __DIR__ . '/DocumentNumbering.php';

class Quotation {
    // Database connection and table name
    private $conn;
    private $table_name = "quotations";

    // Object properties
    public $quotation_id;
    public $quotation_number;
    public $customer_name;
    public $customer_email;
    public $customer_phone;
    public $agency_name;     // New field
    public $agency_address;  // New field
    public $contact_person;  // New field
    public $quotation_date;
    public $valid_until;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;
    public $items = [];

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create quotation
    public function create() {
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Generate quotation number if not provided
            if (empty($this->quotation_number)) {
                $numbering = new DocumentNumbering($this->conn);
                $this->quotation_number = $numbering->generateQuotationNumber();
            }

            // Insert query for quotation
            $query = "INSERT INTO " . $this->table_name . "
                    (quotation_number, customer_name, customer_email, customer_phone, 
                     agency_name, agency_address, contact_person,
                     quotation_date, valid_until, status, notes)
                    VALUES
                    (:quotation_number, :customer_name, :customer_email, :customer_phone, 
                     :agency_name, :agency_address, :contact_person,
                     :quotation_date, :valid_until, :status, :notes)";

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Sanitize data
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

            // Execute query
            $stmt->execute();

            // Get last inserted ID
            $this->quotation_id = $this->conn->lastInsertId();

            // Create quotation items
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
                    // If item creation fails, roll back transaction
                    $this->conn->rollBack();
                    return false;
                }
            }

            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            $this->conn->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // Read all quotations
    public function readAll() {
        // Select all query
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Read single quotation
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE ";
        
        // Check if we're using ID or quotation number
        if (isset($this->quotation_id) && !empty($this->quotation_id)) {
            $query .= "quotation_id = :id LIMIT 0,1";
            $param_name = ":id";
            $param_value = $this->quotation_id;
        } else {
            $query .= "quotation_number = :number LIMIT 0,1";
            $param_name = ":number";
            $param_value = $this->quotation_number;
        }

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam($param_name, $param_value);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set values to object properties
        if ($row) {
            $this->quotation_id = $row['quotation_id'];
            $this->quotation_number = $row['quotation_number'];
            $this->customer_name = $row['customer_name'];
            $this->customer_email = $row['customer_email'];
            $this->customer_phone = $row['customer_phone'];
            $this->agency_name = $row['agency_name'] ?? '';
            $this->agency_address = $row['agency_address'] ?? '';
            $this->contact_person = $row['contact_person'] ?? '';
            $this->quotation_date = $row['quotation_date'];
            $this->valid_until = $row['valid_until'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            // Get quotation items
            $item = new QuotationItem($this->conn);
            $item->quotation_id = $this->quotation_id;
            $this->items = $item->readByQuotationId();

            return true;
        }

        return false;
    }

    // Update quotation
    public function update() {
        try {
            // Begin transaction
            $this->conn->beginTransaction();

            // Update query
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

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Sanitize data
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

            // Execute query
            $stmt->execute();

            // Delete existing items
            $item = new QuotationItem($this->conn);
            $item->quotation_id = $this->quotation_id;
            $item->deleteByQuotationId();

            // Create new items
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
                    // If item creation fails, roll back transaction
                    $this->conn->rollBack();
                    return false;
                }
            }

            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            $this->conn->rollBack();
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    // Delete quotation
    public function delete() {
        // Delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE quotation_id = :quotation_id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->quotation_id = htmlspecialchars(strip_tags($this->quotation_id));

        // Bind id
        $stmt->bindParam(":quotation_id", $this->quotation_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Calculate grand total
    public function calculateGrandTotal() {
        $grand_total = 0;
        
        foreach ($this->items as $item) {
            $grand_total += floatval($item['total_amount']);
        }
        
        return $grand_total;
    }

    // Export quotation as CSV
    public function exportCSV() {
        // Make sure quotation data is loaded
        if (!isset($this->customer_name)) {
            $this->readOne();
        }

        // File name using quotation number
        $filename = $this->quotation_number . '_' . date('Y-m-d') . '.csv';
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        // Add quotation header information
        fputcsv($output, [$this->quotation_number]);
        fputcsv($output, ['Customer:', $this->customer_name]);
        fputcsv($output, ['Agency:', $this->agency_name]);
        fputcsv($output, ['Date:', $this->quotation_date]);
        fputcsv($output, ['Valid Until:', $this->valid_until]);
        fputcsv($output, []);  // Empty row for spacing
        
        // CSV header row
        fputcsv($output, ['Item No', 'Qty', 'Unit', 'Description', 'Original Price', 'Markup (%)', 'Unit Price', 'Total Amount']);
        
        // Add data rows
        foreach ($this->items as $item) {
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
        fputcsv($output, ['', '', '', '', '', '', 'Grand Total:', $this->calculateGrandTotal()]);
        
        // Close output stream
        fclose($output);
        
        return true;
    }
}