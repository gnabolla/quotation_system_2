<?php
/**
 * DeliveryReceipt model class for CRUD operations
 * Path: models/DeliveryReceipt.php
 */

require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/Quotation.php';

class DeliveryReceipt {
    // Database connection and table name
    private $conn;
    private $table_name = "delivery_receipts";
    private $items_table = "delivery_items";

    // Object properties
    public $receipt_id;
    public $quotation_id;
    public $delivery_date;
    public $recipient_name;
    public $recipient_position;
    public $recipient_signature;
    public $delivery_address;
    public $delivery_notes;
    public $delivery_status;
    public $created_at;
    public $updated_at;
    public $items = [];
    
    // Related properties
    public $quotation;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
        $this->quotation = new Quotation($db);
    }

    // Create delivery receipt
    public function create() {
        try {
            // Begin transaction
            $this->conn->beginTransaction();

            // Insert query for delivery receipt
            $query = "INSERT INTO " . $this->table_name . "
                    (quotation_id, delivery_date, recipient_name, recipient_position, 
                     delivery_address, delivery_notes, delivery_status)
                    VALUES
                    (:quotation_id, :delivery_date, :recipient_name, :recipient_position, 
                     :delivery_address, :delivery_notes, :delivery_status)";

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Sanitize data
            $this->quotation_id = htmlspecialchars(strip_tags($this->quotation_id));
            $this->recipient_name = htmlspecialchars(strip_tags($this->recipient_name));
            $this->recipient_position = htmlspecialchars(strip_tags($this->recipient_position));
            $this->delivery_address = htmlspecialchars(strip_tags($this->delivery_address));
            $this->delivery_notes = htmlspecialchars(strip_tags($this->delivery_notes));
            $this->delivery_status = htmlspecialchars(strip_tags($this->delivery_status));

            // Bind values
            $stmt->bindParam(":quotation_id", $this->quotation_id);
            $stmt->bindParam(":delivery_date", $this->delivery_date);
            $stmt->bindParam(":recipient_name", $this->recipient_name);
            $stmt->bindParam(":recipient_position", $this->recipient_position);
            $stmt->bindParam(":delivery_address", $this->delivery_address);
            $stmt->bindParam(":delivery_notes", $this->delivery_notes);
            $stmt->bindParam(":delivery_status", $this->delivery_status);

            // Execute query
            $stmt->execute();

            // Get last inserted ID
            $this->receipt_id = $this->conn->lastInsertId();

            // Create delivery items
            if (!empty($this->items)) {
                $query = "INSERT INTO " . $this->items_table . "
                        (receipt_id, item_id, quantity_delivered)
                        VALUES
                        (:receipt_id, :item_id, :quantity_delivered)";
                
                $stmt = $this->conn->prepare($query);
                
                foreach ($this->items as $item) {
                    $stmt->bindParam(":receipt_id", $this->receipt_id);
                    $stmt->bindParam(":item_id", $item['item_id']);
                    $stmt->bindParam(":quantity_delivered", $item['quantity_delivered']);
                    
                    if (!$stmt->execute()) {
                        // If item creation fails, roll back transaction
                        $this->conn->rollBack();
                        return false;
                    }
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

    // Read all delivery receipts
    public function readAll() {
        // Select all query
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Read delivery receipts by quotation ID
    public function readByQuotationId() {
        // Select all query
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE quotation_id = :quotation_id 
                  ORDER BY created_at DESC";

        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(":quotation_id", $this->quotation_id);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Read single delivery receipt
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE receipt_id = :receipt_id 
                  LIMIT 0,1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(":receipt_id", $this->receipt_id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set values to object properties
        if ($row) {
            $this->quotation_id = $row['quotation_id'];
            $this->delivery_date = $row['delivery_date'];
            $this->recipient_name = $row['recipient_name'];
            $this->recipient_position = $row['recipient_position'];
            $this->recipient_signature = $row['recipient_signature'];
            $this->delivery_address = $row['delivery_address'];
            $this->delivery_notes = $row['delivery_notes'];
            $this->delivery_status = $row['delivery_status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            // Get delivery items
            $this->getDeliveryItems();
            
            // Get quotation details
            $this->quotation->quotation_id = $this->quotation_id;
            $this->quotation->readOne();

            return true;
        }

        return false;
    }

    // Get delivery items
    private function getDeliveryItems() {
        $query = "SELECT di.*, qi.description, qi.unit 
                  FROM " . $this->items_table . " di
                  JOIN quotation_items qi ON di.item_id = qi.item_id
                  WHERE di.receipt_id = :receipt_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":receipt_id", $this->receipt_id);
        $stmt->execute();
        
        $this->items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->items[] = $row;
        }
    }

    // Update delivery receipt
    public function update() {
        try {
            // Begin transaction
            $this->conn->beginTransaction();

            // Update query
            $query = "UPDATE " . $this->table_name . "
                    SET
                        delivery_date = :delivery_date,
                        recipient_name = :recipient_name,
                        recipient_position = :recipient_position,
                        delivery_address = :delivery_address,
                        delivery_notes = :delivery_notes,
                        delivery_status = :delivery_status
                    WHERE
                        receipt_id = :receipt_id";

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Sanitize data
            $this->recipient_name = htmlspecialchars(strip_tags($this->recipient_name));
            $this->recipient_position = htmlspecialchars(strip_tags($this->recipient_position));
            $this->delivery_address = htmlspecialchars(strip_tags($this->delivery_address));
            $this->delivery_notes = htmlspecialchars(strip_tags($this->delivery_notes));
            $this->delivery_status = htmlspecialchars(strip_tags($this->delivery_status));
            $this->receipt_id = htmlspecialchars(strip_tags($this->receipt_id));

            // Bind values
            $stmt->bindParam(":delivery_date", $this->delivery_date);
            $stmt->bindParam(":recipient_name", $this->recipient_name);
            $stmt->bindParam(":recipient_position", $this->recipient_position);
            $stmt->bindParam(":delivery_address", $this->delivery_address);
            $stmt->bindParam(":delivery_notes", $this->delivery_notes);
            $stmt->bindParam(":delivery_status", $this->delivery_status);
            $stmt->bindParam(":receipt_id", $this->receipt_id);

            // Execute query
            $stmt->execute();

            // Update delivery items if needed
            if (!empty($this->items)) {
                // Delete existing items
                $query = "DELETE FROM " . $this->items_table . " WHERE receipt_id = :receipt_id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":receipt_id", $this->receipt_id);
                $stmt->execute();
                
                // Insert new items
                $query = "INSERT INTO " . $this->items_table . "
                        (receipt_id, item_id, quantity_delivered)
                        VALUES
                        (:receipt_id, :item_id, :quantity_delivered)";
                
                $stmt = $this->conn->prepare($query);
                
                foreach ($this->items as $item) {
                    $stmt->bindParam(":receipt_id", $this->receipt_id);
                    $stmt->bindParam(":item_id", $item['item_id']);
                    $stmt->bindParam(":quantity_delivered", $item['quantity_delivered']);
                    
                    if (!$stmt->execute()) {
                        // If item update fails, roll back transaction
                        $this->conn->rollBack();
                        return false;
                    }
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

    // Delete delivery receipt
    public function delete() {
        // Delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE receipt_id = :receipt_id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->receipt_id = htmlspecialchars(strip_tags($this->receipt_id));

        // Bind id
        $stmt->bindParam(":receipt_id", $this->receipt_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Save recipient signature (base64 encoded image)
    public function saveSignature($signature_data) {
        $query = "UPDATE " . $this->table_name . "
                SET recipient_signature = :recipient_signature
                WHERE receipt_id = :receipt_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":recipient_signature", $signature_data);
        $stmt->bindParam(":receipt_id", $this->receipt_id);
        
        return $stmt->execute();
    }
}