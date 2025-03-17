<?php
/**
 * Quotation model class for CRUD operations
 * Path: models/Quotation.php
 */

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/QuotationItem.php';

class Quotation {
    // Database connection and table name
    private $conn;
    private $table_name = "quotations";

    // Object properties
    public $quotation_id;
    public $customer_name;
    public $customer_email;
    public $customer_phone;
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

            // Insert query for quotation
            $query = "INSERT INTO " . $this->table_name . "
                    (customer_name, customer_email, customer_phone, quotation_date, valid_until, status, notes)
                    VALUES
                    (:customer_name, :customer_email, :customer_phone, :quotation_date, :valid_until, :status, :notes)";

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Sanitize data
            $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
            $this->customer_email = htmlspecialchars(strip_tags($this->customer_email));
            $this->customer_phone = htmlspecialchars(strip_tags($this->customer_phone));
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->notes = htmlspecialchars(strip_tags($this->notes));

            // Bind values
            $stmt->bindParam(":customer_name", $this->customer_name);
            $stmt->bindParam(":customer_email", $this->customer_email);
            $stmt->bindParam(":customer_phone", $this->customer_phone);
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
        $query = "SELECT * FROM " . $this->table_name . " WHERE quotation_id = :quotation_id LIMIT 0,1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID of product to be updated
        $stmt->bindParam(":quotation_id", $this->quotation_id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set values to object properties
        if ($row) {
            $this->customer_name = $row['customer_name'];
            $this->customer_email = $row['customer_email'];
            $this->customer_phone = $row['customer_phone'];
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
            $this->status = htmlspecialchars(strip_tags($this->status));
            $this->notes = htmlspecialchars(strip_tags($this->notes));
            $this->quotation_id = htmlspecialchars(strip_tags($this->quotation_id));

            // Bind values
            $stmt->bindParam(":customer_name", $this->customer_name);
            $stmt->bindParam(":customer_email", $this->customer_email);
            $stmt->bindParam(":customer_phone", $this->customer_phone);
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

        // File name
        $filename = 'quotation_' . $this->quotation_id . '_' . date('Y-m-d') . '.csv';
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
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
    
    // Export quotation as Excel
    public function exportExcel() {
        // For Excel export, you would typically use PHPSpreadsheet or similar library
        // This is a placeholder for that functionality
        echo "Excel export would be implemented here";
        return true;
    }
}