<?php
/**
 * Simplified PCPartsQuotation model class
 * Path: models/PCPartsQuotation.php
 */

require_once __DIR__ . '/../config/db_connection.php';

class PCPartsQuotation {
    // Database connection and table name
    private $conn;
    private $table_name = "pc_parts_quotations";
    private $items_table = "pc_parts_quotation_items";

    // Object properties
    public $quotation_id;
    public $quotation_name;
    public $client_name;
    public $created_at;
    public $updated_at;
    public $items = [];

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create PC parts quotation
    public function create() {
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Insert query for quotation header
            $query = "INSERT INTO " . $this->table_name . "
                    (quotation_name, client_name)
                    VALUES
                    (:quotation_name, :client_name)";

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Sanitize data
            $this->quotation_name = htmlspecialchars(strip_tags($this->quotation_name));
            $this->client_name = htmlspecialchars(strip_tags($this->client_name));
            
            // Bind values
            $stmt->bindParam(":quotation_name", $this->quotation_name);
            $stmt->bindParam(":client_name", $this->client_name);

            // Execute query
            $stmt->execute();

            // Get last inserted ID
            $this->quotation_id = $this->conn->lastInsertId();

            // Insert quotation items
            if (!empty($this->items)) {
                $query = "INSERT INTO " . $this->items_table . "
                        (quotation_id, item_id, item_type, description, original_price, quantity, total_price)
                        VALUES
                        (:quotation_id, :item_id, :item_type, :description, :original_price, :quantity, :total_price)";
                
                $stmt = $this->conn->prepare($query);
                
                foreach ($this->items as $item) {
                    // Calculate total price
                    $total_price = $item['original_price'] * $item['quantity'];
                    
                    $stmt->bindParam(":quotation_id", $this->quotation_id);
                    $stmt->bindParam(":item_id", $item['item_id']);
                    $stmt->bindParam(":item_type", $item['item_type']);
                    $stmt->bindParam(":description", $item['description']);
                    $stmt->bindParam(":original_price", $item['original_price']);
                    $stmt->bindParam(":quantity", $item['quantity']);
                    $stmt->bindParam(":total_price", $total_price);
                    
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
            error_log("PCPartsQuotation create error: " . $e->getMessage());
            return false;
        }
    }
    
    // Read all PC parts quotations
    public function readAll() {
        // Select all query
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }
    
    // Read single PC parts quotation
    public function readOne() {
        // Query to read single record
        $query = "SELECT * FROM " . $this->table_name . " WHERE quotation_id = :id LIMIT 0,1";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(":id", $this->quotation_id);

        // Execute query
        $stmt->execute();

        // Get retrieved row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Set values to object properties
            $this->quotation_id = $row['quotation_id'];
            $this->quotation_name = $row['quotation_name'];
            $this->client_name = $row['client_name'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            // Get quotation items
            $this->getQuotationItems();

            return true;
        }

        return false;
    }
    
    // Get quotation items
    private function getQuotationItems() {
        $query = "SELECT * FROM " . $this->items_table . " 
                 WHERE quotation_id = :quotation_id
                 ORDER BY description";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quotation_id", $this->quotation_id);
        $stmt->execute();
        
        $this->items = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->items[] = $row;
        }
    }
    
    // Update PC parts quotation
    public function update() {
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Update query for quotation header
            $query = "UPDATE " . $this->table_name . "
                    SET
                        quotation_name = :quotation_name,
                        client_name = :client_name
                    WHERE
                        quotation_id = :quotation_id";

            // Prepare statement
            $stmt = $this->conn->prepare($query);

            // Sanitize data
            $this->quotation_name = htmlspecialchars(strip_tags($this->quotation_name));
            $this->client_name = htmlspecialchars(strip_tags($this->client_name));
            
            // Bind values
            $stmt->bindParam(":quotation_name", $this->quotation_name);
            $stmt->bindParam(":client_name", $this->client_name);
            $stmt->bindParam(":quotation_id", $this->quotation_id);

            // Execute query
            $stmt->execute();

            // Update quotation items: first delete existing items
            $query = "DELETE FROM " . $this->items_table . " WHERE quotation_id = :quotation_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quotation_id", $this->quotation_id);
            $stmt->execute();
            
            // Insert updated items
            if (!empty($this->items)) {
                $query = "INSERT INTO " . $this->items_table . "
                        (quotation_id, item_id, item_type, description, original_price, quantity, total_price)
                        VALUES
                        (:quotation_id, :item_id, :item_type, :description, :original_price, :quantity, :total_price)";
                
                $stmt = $this->conn->prepare($query);
                
                foreach ($this->items as $item) {
                    // Calculate total price
                    $total_price = $item['original_price'] * $item['quantity'];
                    
                    $stmt->bindParam(":quotation_id", $this->quotation_id);
                    $stmt->bindParam(":item_id", $item['item_id']);
                    $stmt->bindParam(":item_type", $item['item_type']);
                    $stmt->bindParam(":description", $item['description']);
                    $stmt->bindParam(":original_price", $item['original_price']);
                    $stmt->bindParam(":quantity", $item['quantity']);
                    $stmt->bindParam(":total_price", $total_price);
                    
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
            error_log("PCPartsQuotation update error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete PC parts quotation
    public function delete() {
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Delete items first
            $query = "DELETE FROM " . $this->items_table . " WHERE quotation_id = :quotation_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quotation_id", $this->quotation_id);
            $stmt->execute();
            
            // Delete quotation
            $query = "DELETE FROM " . $this->table_name . " WHERE quotation_id = :quotation_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quotation_id", $this->quotation_id);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            $this->conn->rollBack();
            error_log("PCPartsQuotation delete error: " . $e->getMessage());
            return false;
        }
    }
    
    // Calculate grand total
    public function calculateGrandTotal() {
        $grand_total = 0;
        
        foreach ($this->items as $item) {
            $grand_total += floatval($item['total_price']);
        }
        
        return $grand_total;
    }
}