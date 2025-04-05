<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Quotation System'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../css/styles.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Quotation System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../quotation/list.php">Quotations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../quotation/create.php">Create Quotation</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../delivery/list.php">Delivery Receipts</a>
                    </li>
                    <!-- New PC Parts Quotation navigation item -->
                    <li class="nav-item">
                        <a class="nav-link" href="../pcparts/list.php">PC Parts Quotations</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>