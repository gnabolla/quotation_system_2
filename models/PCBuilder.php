<?php
/**
 * PCBuilder model class for managing PC configurations
 * Path: models/PCBuilder.php
 */

class PCBuilder {
    // Database connection and table names
    private $conn;
    private $bundles_table = "bundles";
    private $items_table = "items";
    private $categories_table = "categories";
    
    // Object properties
    public $configuration = [
        'bundle' => null,
        'items' => [],
        'custom_items' => []
    ];
    
    // Constructor with database connection
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all available bundles
     * 
     * @return PDOStatement Bundles result set
     */
    public function getBundles() {
        $query = "SELECT * FROM " . $this->bundles_table . " ORDER BY bundle_type, price";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Get bundle by ID
     * 
     * @param int $bundle_id Bundle ID
     * @return array|null Bundle data or null if not found
     */
    public function getBundleById($bundle_id) {
        $query = "SELECT * FROM " . $this->bundles_table . " WHERE bundle_id = :bundle_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":bundle_id", $bundle_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
        
        return null;
    }
    
    /**
     * Get all available items by category
     * 
     * @param int|null $category_id Optional category ID to filter by
     * @return array Items grouped by category
     */
    public function getItemsByCategory($category_id = null) {
        $query = "SELECT i.*, c.category_name 
                FROM " . $this->items_table . " i
                JOIN " . $this->categories_table . " c ON i.category_id = c.category_id";
        
        if ($category_id !== null) {
            $query .= " WHERE i.category_id = :category_id";
        }
        
        $query .= " ORDER BY c.category_name, i.item_name";
        
        $stmt = $this->conn->prepare($query);
        
        if ($category_id !== null) {
            $stmt->bindParam(":category_id", $category_id);
        }
        
        $stmt->execute();
        
        $items_by_category = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $category = $row['category_name'];
            if (!isset($items_by_category[$category])) {
                $items_by_category[$category] = [];
            }
            $items_by_category[$category][] = $row;
        }
        
        return $items_by_category;
    }
    
    /**
     * Get item by ID
     * 
     * @param int $item_id Item ID
     * @return array|null Item data or null if not found
     */
    public function getItemById($item_id) {
        $query = "SELECT i.*, c.category_name 
                FROM " . $this->items_table . " i
                JOIN " . $this->categories_table . " c ON i.category_id = c.category_id
                WHERE i.item_id = :item_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":item_id", $item_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }
        
        return null;
    }
    
    /**
     * Get all available categories
     * 
     * @return PDOStatement Categories result set
     */
    public function getCategories() {
        $query = "SELECT * FROM " . $this->categories_table . " ORDER BY category_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     * Calculate total price of the configuration
     * 
     * @return float Total price
     */
    public function calculateTotalPrice() {
        $total = 0;
        
        // Add bundle price
        if (!empty($this->configuration['bundle'])) {
            $total += floatval($this->configuration['bundle']['price']);
        }
        
        // Add selected items prices
        foreach ($this->configuration['items'] as $item) {
            $total += floatval($item['price']) * floatval($item['quantity']);
        }
        
        // Add custom items prices
        foreach ($this->configuration['custom_items'] as $item) {
            $total += floatval($item['price']) * floatval($item['quantity']);
        }
        
        return $total;
    }
    
    /**
     * Add a bundle to the configuration
     * 
     * @param int $bundle_id Bundle ID
     * @return bool Success or failure
     */
    public function addBundle($bundle_id) {
        $bundle = $this->getBundleById($bundle_id);
        if ($bundle) {
            $this->configuration['bundle'] = $bundle;
            return true;
        }
        return false;
    }
    
    /**
     * Add an item to the configuration
     * 
     * @param int $item_id Item ID
     * @param float $quantity Quantity of the item
     * @return bool Success or failure
     */
    public function addItem($item_id, $quantity = 1) {
        $item = $this->getItemById($item_id);
        if ($item) {
            // Check if the item already exists in the configuration
            foreach ($this->configuration['items'] as $key => $existing_item) {
                if ($existing_item['item_id'] == $item_id) {
                    // Update quantity
                    $this->configuration['items'][$key]['quantity'] += $quantity;
                    return true;
                }
            }
            
            // Add new item
            $item['quantity'] = $quantity;
            $this->configuration['items'][] = $item;
            return true;
        }
        return false;
    }
    
    /**
     * Add a custom item to the configuration
     * 
     * @param string $item_name Custom item name
     * @param float $price Custom item price
     * @param float $quantity Quantity of the custom item
     * @param string $category_name Category name (optional)
     * @return bool Success or failure
     */
    public function addCustomItem($item_name, $price, $quantity = 1, $category_name = 'Custom') {
        if (!empty($item_name) && is_numeric($price) && $price >= 0) {
            $custom_item = [
                'item_id' => 'custom_' . uniqid(),
                'item_name' => $item_name,
                'price' => $price,
                'quantity' => $quantity,
                'category_name' => $category_name
            ];
            
            $this->configuration['custom_items'][] = $custom_item;
            return true;
        }
        return false;
    }
    
    /**
     * Remove an item from the configuration
     * 
     * @param string $item_id Item ID or custom item ID
     * @param string $type Type of item ('regular' or 'custom')
     * @return bool Success or failure
     */
    public function removeItem($item_id, $type = 'regular') {
        if ($type === 'regular') {
            foreach ($this->configuration['items'] as $key => $item) {
                if ($item['item_id'] == $item_id) {
                    unset($this->configuration['items'][$key]);
                    $this->configuration['items'] = array_values($this->configuration['items']);
                    return true;
                }
            }
        } else if ($type === 'custom') {
            foreach ($this->configuration['custom_items'] as $key => $item) {
                if ($item['item_id'] == $item_id) {
                    unset($this->configuration['custom_items'][$key]);
                    $this->configuration['custom_items'] = array_values($this->configuration['custom_items']);
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Remove the bundle from the configuration
     * 
     * @return bool Success or failure
     */
    public function removeBundle() {
        $this->configuration['bundle'] = null;
        return true;
    }
    
    /**
     * Update the quantity of an item
     * 
     * @param string $item_id Item ID or custom item ID
     * @param float $quantity New quantity
     * @param string $type Type of item ('regular' or 'custom')
     * @return bool Success or failure
     */
    public function updateItemQuantity($item_id, $quantity, $type = 'regular') {
        if ($quantity <= 0) {
            return $this->removeItem($item_id, $type);
        }
        
        if ($type === 'regular') {
            foreach ($this->configuration['items'] as $key => $item) {
                if ($item['item_id'] == $item_id) {
                    $this->configuration['items'][$key]['quantity'] = $quantity;
                    return true;
                }
            }
        } else if ($type === 'custom') {
            foreach ($this->configuration['custom_items'] as $key => $item) {
                if ($item['item_id'] == $item_id) {
                    $this->configuration['custom_items'][$key]['quantity'] = $quantity;
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Reset the configuration
     * 
     * @return bool Always returns true
     */
    public function resetConfiguration() {
        $this->configuration = [
            'bundle' => null,
            'items' => [],
            'custom_items' => []
        ];
        return true;
    }
    
    /**
     * Export the configuration to an array for creating quotations
     * 
     * @param string $customer_name Customer name for the quotation
     * @param float $markup_percentage Markup percentage to apply
     * @return array Configuration data formatted for quotation
     */
    public function exportForQuotation($customer_name, $markup_percentage = 0) {
        $quotation_data = [
            'customer_name' => $customer_name,
            'quotation_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'draft',
            'items' => []
        ];
        
        $item_no = 1;
        
        // Add bundle
        if (!empty($this->configuration['bundle'])) {
            $bundle = $this->configuration['bundle'];
            $original_price = $bundle['price'];
            $unit_price = $original_price * (1 + ($markup_percentage / 100));
            $total_amount = $unit_price;
            
            $quotation_data['items'][] = [
                'item_no' => $item_no++,
                'quantity' => 1,
                'unit' => 'set',
                'description' => $bundle['bundle_type'] . ' Bundle: ' . $bundle['processor'] . ' + ' . $bundle['motherboard'],
                'original_price' => $original_price,
                'markup_percentage' => $markup_percentage,
                'unit_price' => $unit_price,
                'total_amount' => $total_amount
            ];
        }
        
        // Add regular items
        foreach ($this->configuration['items'] as $item) {
            $original_price = $item['price'];
            $quantity = $item['quantity'];
            $unit_price = $original_price * (1 + ($markup_percentage / 100));
            $total_amount = $unit_price * $quantity;
            
            $quotation_data['items'][] = [
                'item_no' => $item_no++,
                'quantity' => $quantity,
                'unit' => 'pcs',
                'description' => $item['item_name'] . ' (' . $item['category_name'] . ')',
                'original_price' => $original_price,
                'markup_percentage' => $markup_percentage,
                'unit_price' => $unit_price,
                'total_amount' => $total_amount
            ];
        }
        
        // Add custom items
        foreach ($this->configuration['custom_items'] as $item) {
            $original_price = $item['price'];
            $quantity = $item['quantity'];
            $unit_price = $original_price * (1 + ($markup_percentage / 100));
            $total_amount = $unit_price * $quantity;
            
            $quotation_data['items'][] = [
                'item_no' => $item_no++,
                'quantity' => $quantity,
                'unit' => 'pcs',
                'description' => $item['item_name'] . ' (' . $item['category_name'] . ') - Custom Item',
                'original_price' => $original_price,
                'markup_percentage' => $markup_percentage,
                'unit_price' => $unit_price,
                'total_amount' => $total_amount
            ];
        }
        
        return $quotation_data;
    }
    
    /**
     * Save configuration to session
     * 
     * @return bool Always returns true
     */
    public function saveToSession() {
        $_SESSION['pc_builder_config'] = $this->configuration;
        return true;
    }
    
    /**
     * Load configuration from session
     * 
     * @return bool Success or failure
     */
    public function loadFromSession() {
        if (isset($_SESSION['pc_builder_config'])) {
            $this->configuration = $_SESSION['pc_builder_config'];
            return true;
        }
        return false;
    }
}