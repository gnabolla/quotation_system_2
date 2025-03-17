<?php
/**
 * QuotationItem model class for CRUD operations
 * Path: models/QuotationItem.php
 */

class QuotationItem {
    // Database connection and table name
    private $conn;
    private $table_name = "quotation_items";

    // Object properties
    public $item_id;
    public $quotation_id;
    public $item_no;
    public $quantity;
    public $unit;
    public $description;
    public $original_price;
    public $markup_percentage;
    public $unit_price;
    public $total_amount;

    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create quotation item
    public function create() {
        // Insert query
        $query = "INSERT INTO " . $this->table_name . "
                (quotation_id, item_no, quantity, unit, description, original_price, markup_percentage, unit_price, total_amount)
                VALUES
                (:quotation_id, :item_no, :quantity, :unit, :description, :original_price, :markup_percentage, :unit_price, :total_amount)";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $this->quotation_id = htmlspecialchars(strip_tags($this->quotation_id));
        $this->item_no = htmlspecialchars(strip_tags($this->item_no));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->unit = htmlspecialchars(strip_tags($this->unit));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->original_price = htmlspecialchars(strip_tags($this->original_price));
        $this->markup_percentage = htmlspecialchars(strip_tags($this->markup_percentage));
        $this->unit_price = htmlspecialchars(strip_tags($this->unit_price));
        $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));

        // Bind values
        $stmt->bindParam(":quotation_id", $this->quotation_id);
        $stmt->bindParam(":item_no", $this->item_no);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":unit", $this->unit);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":original_price", $this->original_price);
        $stmt->bindParam(":markup_percentage", $this->markup_percentage);
        $stmt->bindParam(":unit_price", $this->unit_price);
        $stmt->bindParam(":total_amount", $this->total_amount);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read items by quotation ID
    public function readByQuotationId() {
        // Select all query
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE quotation_id = :quotation_id 
                  ORDER BY item_no ASC";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(":quotation_id", $this->quotation_id);

        // Execute query
        $stmt->execute();

        $items = [];
        
        // Fetch all rows
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $row['item_id'],
                'quotation_id' => $row['quotation_id'],
                'item_no' => $row['item_no'],
                'quantity' => $row['quantity'],
                'unit' => $row['unit'],
                'description' => $row['description'],
                'original_price' => $row['original_price'],
                'markup_percentage' => $row['markup_percentage'],
                'unit_price' => $row['unit_price'],
                'total_amount' => $row['total_amount']
            ];
        }

        return $items;
    }

    // Delete items by quotation ID
    public function deleteByQuotationId() {
        // Delete query
        $query = "DELETE FROM " . $this->table_name . " WHERE quotation_id = :quotation_id";

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(":quotation_id", $this->quotation_id);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Calculate unit price based on original price and markup
    public static function calculateUnitPrice($original_price, $markup_percentage) {
        return $original_price * (1 + ($markup_percentage / 100));
    }

    // Calculate total amount based on quantity and unit price
    public static function calculateTotalAmount($quantity, $unit_price) {
        return $quantity * $unit_price;
    }
}