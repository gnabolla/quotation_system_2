<?php
function calculateGovernmentSaleProfit($cost, $markupPercent = null, $sellingPrice = null, $desiredNet = null, $desiredNetPercent = null) {
    if ($desiredNetPercent !== null) {
        $desiredNet = $cost * ($desiredNetPercent / 100);
        $sellingPrice = ($cost + $desiredNet + ($cost + $desiredNet) * 0.03) / 0.96;
        $markupAmount = $sellingPrice - $cost;
        $markupPercent = ($markupAmount / $cost) * 100;
    } elseif ($desiredNet !== null) {
        $sellingPrice = ($cost + $desiredNet + ($cost + $desiredNet) * 0.03) / 0.96;
        $markupAmount = $sellingPrice - $cost;
        $markupPercent = ($markupAmount / $cost) * 100;
    } elseif ($markupPercent !== null) {
        $markupAmount = $cost * ($markupPercent / 100);
        $sellingPrice = $cost + $markupAmount;
    } elseif ($sellingPrice !== null) {
        $markupAmount = $sellingPrice - $cost;
        $markupPercent = ($markupAmount / $cost) * 100;
    } else {
        return null; // No input to compute
    }

    $percentageTax = $sellingPrice * 0.03;
    $withholdingTax = $sellingPrice * 0.04;
    $cashReceived = $sellingPrice - $withholdingTax;
    $netProfit = $cashReceived - $cost - $percentageTax;

    return [
        'cost' => number_format($cost, 2),
        'selling_price' => number_format($sellingPrice, 2),
        'markup_amount' => number_format($markupAmount, 2),
        'markup_percent' => number_format($markupPercent, 2),
        'percentage_tax' => number_format($percentageTax, 2),
        'withholding_tax' => number_format($withholdingTax, 2),
        'cash_received' => number_format($cashReceived, 2),
        'net_profit' => number_format($netProfit, 2),
    ];
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cost = isset($_POST['cost']) ? floatval($_POST['cost']) : 0;
    $markupPercent = isset($_POST['markup']) && $_POST['markup'] !== '' ? floatval($_POST['markup']) : null;
    $sellingPrice = isset($_POST['selling_price']) && $_POST['selling_price'] !== '' ? floatval($_POST['selling_price']) : null;
    $desiredNet = isset($_POST['desired_net']) && $_POST['desired_net'] !== '' ? floatval($_POST['desired_net']) : null;
    $desiredNetPercent = isset($_POST['desired_net_percent']) && $_POST['desired_net_percent'] !== '' ? floatval($_POST['desired_net_percent']) : null;

    if ($markupPercent !== null || $sellingPrice !== null || $desiredNet !== null || $desiredNetPercent !== null) {
        $result = calculateGovernmentSaleProfit($cost, $markupPercent, $sellingPrice, $desiredNet, $desiredNetPercent);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gov Client Profit Calculator</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; background: #f4f4f4; padding: 20px; border-radius: 8px; }
        input[type="number"] { width: 100%; padding: 8px; margin: 8px 0; }
        input[type="submit"] { background: #4CAF50; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        .result { background: #fff; padding: 15px; border-radius: 8px; margin-top: 20px; }
        small { color: #555; }
    </style>
</head>
<body>
    <h2>Government Sales Profit Calculator</h2>
    <form method="POST">
        <label for="cost">Item Cost (₱):</label>
        <input type="number" name="cost" id="cost" step="0.01" required>

        <label for="markup">Markup Percentage (%):</label>
        <input type="number" name="markup" id="markup" step="0.01">
        <small>Leave blank if you want to enter a selling price or desired net profit</small>

        <label for="selling_price">Selling Price (₱):</label>
        <input type="number" name="selling_price" id="selling_price" step="0.01">
        <small>Leave blank if you're using markup or desired net profit</small>

        <label for="desired_net">Desired Net Profit (₱):</label>
        <input type="number" name="desired_net" id="desired_net" step="0.01">
        <small>Enter this only if you want to compute the price needed to hit a specific take-home profit</small>

        <label for="desired_net_percent">Desired Net Profit (% of Cost):</label>
        <input type="number" name="desired_net_percent" id="desired_net_percent" step="0.01">
        <small>Automatically calculates based on percentage of cost (e.g. 15%)</small>

        <input type="submit" value="Calculate">
    </form>

    <?php if ($result): ?>
        <div class="result">
            <h3>Calculation Result</h3>
            <p><strong>Cost:</strong> ₱<?= $result['cost'] ?></p>
            <p><strong>Selling Price:</strong> ₱<?= $result['selling_price'] ?></p>
            <p><strong>Markup Amount:</strong> ₱<?= $result['markup_amount'] ?></p>
            <p><strong>Markup Percentage:</strong> <?= $result['markup_percent'] ?>%</p>
            <p><strong>3% Percentage Tax:</strong> ₱<?= $result['percentage_tax'] ?></p>
            <p><strong>4% Withholding Tax:</strong> ₱<?= $result['withholding_tax'] ?></p>
            <p><strong>Cash Received:</strong> ₱<?= $result['cash_received'] ?></p>
            <p><strong>Net Profit:</strong> ₱<?= $result['net_profit'] ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
