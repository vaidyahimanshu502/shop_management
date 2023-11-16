<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .subtotal {
            text-align: right;
            margin-top: 20px;
        }
        .btn-container {
            display: flex;
            flex-direction: row;
            justify-content: center;
            align-items : center;
        }
    </style>
</head>
<body>
<?php
// Replace with your actual database connection details
include("../../config/db_connect.php");
include("../../partials/header.php");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming you have the purchase ID in the URL parameter 'id'
$purchaseId = isset($_GET['id']) ? $_GET['id'] : null;

if ($purchaseId !== null) {
    // SQL query to fetch all items for a specific purchase
    $sql = "
        SELECT
            p.id AS purchase_id,
            p.invoice_no,
            p.invoice_date,
            s.supplier_name,
            pd.quantity,
            i.item_name,
            i.price,
            pd.amount
        FROM
            purchase p
        JOIN
            supplier s ON p.supplier_id = s.id
        JOIN
            purchase_details pd ON p.id = pd.purchase_id
        JOIN
            items i ON pd.item_id = i.id
        WHERE
            p.id = $purchaseId;
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $subtotal = 0; // Initialize subtotal

        // Display purchase details
        $row = $result->fetch_assoc();
        ?>
        <div class="report-header mt-4">
            <div>
                <p><strong>Invoice Number:</strong> <?php echo $row["invoice_no"]; ?></p>
            </div>
            <div>
                <p><strong>Invoice Date:</strong> <?php echo $row["invoice_date"]; ?></p>
            </div>
        </div>

        <div class="report-header mt-1">
            <div>
                <p><strong>Supplier Name:</strong> <?php echo $row["supplier_name"]; ?></p>
            </div>
        </div>

        <table class="mt-2">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display all items
                do {
                    ?>
                    <tr>
                        <td><?php echo $row["item_name"]; ?></td>
                        <td><?php echo $row["price"]; ?></td>
                        <td><?php echo $row["quantity"]; ?></td>
                        <td><?php echo $row["amount"]; ?></td>
                    </tr>
                    <?php
                    $subtotal += $row["amount"]; // Update subtotal
                } while ($row = $result->fetch_assoc());
                ?>
            </tbody>
        </table>

        <?php
        // Display subtotal
        ?>
        <div class="subtotal">
            <p><strong>Subtotal:</strong> <?php echo $subtotal; ?></p>
        </div>
        <?php
    } else {
        echo "No results found for the specified purchase ID.";
    }
} else {
    echo "Purchase ID not provided in the URL.";
}

$conn->close();
?>
<div class="btn-container">
  <a href="/shop_management/reports/purchaseReport.php" class="btn btn-secondary">Go back to purchase report</a>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
