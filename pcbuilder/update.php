<?php
/**
 * PC Builder update handler
 * Path: pcbuilder/update.php
 */

// Start session
session_start();

// Include database and object files
require_once '../config/db_connection.php';
require_once '../models/PCBuilder.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Initialize PCBuilder object
$pcBuilder = new PCBuilder($db);

// Load configuration from session if exists
$pcBuilder->loadFromSession();

// Get action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Process action
switch ($action) {
    case 'add_bundle':
        // Get bundle_id from request
        $bundle_id = isset($_GET['bundle_id']) ? intval($_GET['bundle_id']) : 0;
        
        // Add bundle to configuration
        if ($bundle_id > 0) {
            $pcBuilder->addBundle($bundle_id);
            $pcBuilder->saveToSession();
        }
        
        // Redirect back to bundle page
        header("Location: bundle.php");
        break;
        
    case 'remove_bundle':
        // Remove bundle from configuration
        $pcBuilder->removeBundle();
        $pcBuilder->saveToSession();
        
        // Redirect based on referer
        if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'bundle.php') !== false) {
            header("Location: bundle.php");
        } else {
            header("Location: index.php");
        }
        break;
        
    case 'add_item':
        // Get item_id and quantity from request
        $item_id = isset($_REQUEST['item_id']) ? intval($_REQUEST['item_id']) : 0;
        $quantity = isset($_REQUEST['quantity']) ? intval($_REQUEST['quantity']) : 1;
        
        // Add item to configuration
        if ($item_id > 0 && $quantity > 0) {
            $pcBuilder->addItem($item_id, $quantity);
            $pcBuilder->saveToSession();
        }
        
        // Redirect back to items page
        header("Location: items.php" . (isset($_GET['category_id']) ? "?category_id=" . $_GET['category_id'] : ""));
        break;
        
    case 'remove_item':
        // Get item_id and type from request
        $item_id = isset($_GET['item_id']) ? $_GET['item_id'] : '';
        $type = isset($_GET['type']) ? $_GET['type'] : 'regular';
        
        // Remove item from configuration
        if (!empty($item_id)) {
            $pcBuilder->removeItem($item_id, $type);
            $pcBuilder->saveToSession();
        }
        
        // Redirect based on referer
        if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'items.php') !== false) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'custom.php') !== false) {
            header("Location: custom.php");
        } else {
            header("Location: index.php");
        }
        break;
        
    case 'update_quantity':
        // Get item_id, quantity, and type from request
        $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : '';
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
        $type = isset($_POST['type']) ? $_POST['type'] : 'regular';
        
        // Update item quantity in configuration
        if (!empty($item_id) && $quantity > 0) {
            $pcBuilder->updateItemQuantity($item_id, $quantity, $type);
            $pcBuilder->saveToSession();
        }
        
        // Redirect based on referer
        if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'items.php') !== false) {
            header("Location: " . $_SERVER['HTTP_REFERER']);
        } else if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'custom.php') !== false) {
            header("Location: custom.php");
        } else {
            header("Location: index.php");
        }
        break;
        
    default:
        // Unknown action, redirect to PC Builder index
        header("Location: index.php");
        break;
}
?>