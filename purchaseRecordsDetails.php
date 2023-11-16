<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMS-Purchase Records-Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include("./partials/header.php") ?>
    <div class="container mx-auto m-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center text-primary">Purchase Records</h1>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">S. No.</th>
                            <th scope="col">Supplier Name</th>
                            <th scope="col">Invoice No</th>
                            <th scope="col">Date</th>
                            <th scope="col">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include("./config/db_connect.php");

                        $sql = "SELECT purchase.*, supplier.supplier_name AS supplier_name
                                FROM purchase
                                INNER JOIN supplier ON purchase.supplier_id = supplier.id
                                GROUP BY purchase.id ORDER BY id DESC";

                        $result = $conn->query($sql);
                        $counter = 1; // Initialize the counter

                        while ($row = $result->fetch_assoc()) {
                            echo '
                                <tr>
                                    <th scope="row">' . $counter . '</th>
                                    <td>' . $row['supplier_name'] . '</td>
                                    <td>' . $row['invoice_no'] . '</td>
                                    <td>' . $row['invoice_date'] . '</td>
                                    <td>' . $row['total_amount'] . '</td>
                                </tr>
                                <tr>
                                    <td colspan="7">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Item Name</th>
                                                    <th scope="col">Quantity</th>
                                                    <th scope="col">Price</th>
                                                    <th scope="col">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>';

                            // Fetch purchase details for the current purchase record
                            $purchaseDetailsSql = "SELECT 
                                                    items.item_name,
                                                    purchase_details.quantity,
                                                    purchase_details.price,
                                                    purchase_details.amount
                                                FROM purchase_details 
                                                INNER JOIN items ON purchase_details.item_id = items.id
                                                WHERE purchase_details.purchase_id = " . $row['id'];

                            $purchaseDetailsResult = $conn->query($purchaseDetailsSql);

                            while ($purchaseDetailsRow = $purchaseDetailsResult->fetch_assoc()) {
                                echo '
                                    <tr>
                                        <td>' . $purchaseDetailsRow['item_name'] . '</td>
                                        <td>' . $purchaseDetailsRow['quantity'] . '</td>
                                        <td>' . $purchaseDetailsRow['price'] . '</td>
                                        <td>' . $purchaseDetailsRow['amount'] . '</td>
                                    </tr>';
                            }

                            echo '
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>';
                            $counter++; // Increment the counter for the next iteration
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
