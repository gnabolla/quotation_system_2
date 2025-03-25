<?php
/**
 * DocumentNumbering model class for consistent number generation
 * Path: models/DocumentNumbering.php
 */

class DocumentNumbering {
    // Database connection
    private $conn;
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Generate a new document number
     * 
     * @param string $type Document type prefix (QUO, DR)
     * @param string $table Table name to check for existing numbers
     * @param string $column Column name containing the document number
     * @return string Formatted document number
     */
    public function generateNumber($type, $table, $column) {
        // Get current date in YYYYMMDD format
        $date = date('Ymd');
        
        // Format is PREFIX-YYYYMMDD-XXX where XXX is sequential
        $datePrefix = substr($date, 0, 8); // Get YYYYMMDD part
        $basePrefix = $type . "-" . $datePrefix;
        
        // Find the highest existing number for today
        $query = "SELECT MAX(" . $column . ") as max_number FROM " . $table . " 
                  WHERE " . $column . " LIKE :prefix";
        
        $stmt = $this->conn->prepare($query);
        
        // Add wildcard to match all numbers with this date prefix
        $searchPrefix = $basePrefix . "%";
        $stmt->bindParam(":prefix", $searchPrefix);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $maxNumber = $row['max_number'];
        
        if ($maxNumber) {
            // Extract the sequential part
            $parts = explode('-', $maxNumber);
            $sequential = (int)end($parts);
            $sequential++;
        } else {
            // Start with 1 if no existing numbers
            $sequential = 1;
        }
        
        // Format with leading zeros (e.g., 001, 010, 100)
        $sequentialPadded = str_pad($sequential, 3, '0', STR_PAD_LEFT);
        
        // Combine to create final number
        return $basePrefix . "-" . $sequentialPadded;
    }
    
    /**
     * Generate a new quotation number
     * 
     * @return string Formatted quotation number (QUO-YYYYMMDD-XXX)
     */
    public function generateQuotationNumber() {
        return $this->generateNumber('QUO', 'quotations', 'quotation_number');
    }
    
    /**
     * Generate a new delivery receipt number
     * 
     * @return string Formatted delivery receipt number (DR-YYYYMMDD-XXX)
     */
    public function generateDeliveryReceiptNumber() {
        return $this->generateNumber('DR', 'delivery_receipts', 'receipt_number');
    }
}