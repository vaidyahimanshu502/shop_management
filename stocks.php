<?php
// Include your database connection file
include('config/db_connect.php');

// Reset $stockData array
$stockData = array();

// Fetch data from items table
$sqlStockData = "
    SELECT
        i.id AS item_id,
        i.item_name,
        COALESCE(purchase.quantity, 0) AS purchase_quantity,
        COALESCE(sale.quantity, 0) AS sale_quantity
    FROM
        items i
    LEFT JOIN (
        SELECT
            item_id,
            COALESCE(SUM(quantity), 0) AS quantity
        FROM
            purchase_details
        GROUP BY
            item_id
    ) purchase ON i.id = purchase.item_id
    LEFT JOIN (
        SELECT
            item_id,
            COALESCE(SUM(qty), 0) AS quantity
        FROM
            sale_details
        GROUP BY
            item_id
    ) sale ON i.id = sale.item_id
";

$resultStockData = $conn->query($sqlStockData);

if ($resultStockData) {
    while ($rowStockData = $resultStockData->fetch_assoc()) {
        $item_id = $rowStockData['item_id'];
        $purchase_quantity = $rowStockData['purchase_quantity'];
        $sale_quantity = $rowStockData['sale_quantity'];

        $stockData[$item_id] = array(
            'item_name' => $rowStockData['item_name'],
            'purchase' => $purchase_quantity,
            'sale' => $sale_quantity,
        );
    }
} else {
    echo '<script>alert("Error in fetching stock data")</script>';
}

// Display stock report
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>
<?php include("./partials/header.php") ?>
    <div class="container my-2">
        <h1 class="text-center text-secondary">Stock Report</h1>
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Item Name</th>
                    <th scope="col">Purchase Quantity</th>
                    <th scope="col">Sale Quantity</th>
                    <th scope="col">Stock Difference</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display stock report
                foreach ($stockData as $item_id => $data) {
                    $item_name = $data['item_name'];
                    $purchase_quantity = $data['purchase'];
                    $sale_quantity = $data['sale'];

                    echo '<tr>';
                    echo '<td>' . $item_name . '</td>';
                    echo '<td>' . $purchase_quantity . '</td>';
                    echo '<td>' . $sale_quantity . '</td>';
                    
                    // Calculate and display the stock difference
                    $stockDifference = $purchase_quantity - $sale_quantity;
                    echo '<td>' . $stockDifference . '</td>';

                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
