<?php
/**
 * PC Builder bundle selection page
 * Path: pcbuilder/bundle.php
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

// Get all available bundles
$bundles_stmt = $pcBuilder->getBundles();

// Group bundles by type for better display
$bundles_by_type = [];
while ($row = $bundles_stmt->fetch(PDO::FETCH_ASSOC)) {
    $type = $row['bundle_type'];
    if (!isset($bundles_by_type[$type])) {
        $bundles_by_type[$type] = [];
    }
    $bundles_by_type[$type][] = $row;
}

// Page title
$page_title = "Select a Bundle";

// Include header
include_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Select a Bundle (Processor + Motherboard)</h2>
                <a href="index.php" class="btn btn-secondary">Back to Builder</a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <!-- Current Bundle Selection -->
            <?php if (!empty($pcBuilder->configuration['bundle'])): 
                $bundle = $pcBuilder->configuration['bundle'];
            ?>
            <div class="alert alert-info">
                <h5>Currently Selected Bundle:</h5>
                <p><?php echo $bundle['bundle_type'] . ': ' . $bundle['processor'] . ' + ' . $bundle['motherboard']; ?> - ₱<?php echo number_format($bundle['price'], 2); ?></p>
                <a href="update.php?action=remove_bundle" class="btn btn-danger btn-sm">Remove this bundle</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bundles by Type -->
    <?php foreach ($bundles_by_type as $type => $bundles): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><?php echo $type; ?> Bundles</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Processor</th>
                                    <th>Motherboard</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bundles as $bundle): ?>
                                <tr>
                                    <td><?php echo $bundle['processor']; ?></td>
                                    <td><?php echo $bundle['motherboard']; ?></td>
                                    <td>₱<?php echo number_format($bundle['price'], 2); ?></td>
                                    <td>
                                        <?php if (!empty($pcBuilder->configuration['bundle']) && $pcBuilder->configuration['bundle']['bundle_id'] == $bundle['bundle_id']): ?>
                                        <button class="btn btn-success btn-sm" disabled>Selected</button>
                                        <?php else: ?>
                                        <a href="update.php?action=add_bundle&bundle_id=<?php echo $bundle['bundle_id']; ?>" class="btn btn-primary btn-sm">Select</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($bundles_by_type)): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                No bundles available. Please contact the administrator to add some bundles.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>