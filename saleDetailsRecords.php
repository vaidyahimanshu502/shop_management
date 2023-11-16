<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Sale Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include("./partials/header.php") ?>
    <?php
    // Include your database connection file
    include('./config/db_connect.php');

    // Function to get item name by item_id
    function getItemName($itemId, $conn)
    {
        $stmt = $conn->prepare("SELECT item_name FROM items WHERE id = ?");
        $stmt->bind_param('i', $itemId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['item_name'];
        }

        return 'Item Not Found'; // You can customize the default value as needed
    }
    ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                <h1 class="text-center text-secondary display-3">Sale Records</h1>
                    <thead>
                        <tr>
                            <th scope="row">S. No</th>
                            <th scope="row">Customer Name</th>
                            <th scope="row">Mobile No</th>
                            <th scope="row">Invoice No</th>
                            <th scope="row">Date of Sale</th>
                            <th scope="row">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM sale ORDER BY created_at DESC";
                        $result = $conn->query($sql);
                        $counter = 1; // Initialize counter
                        while ($row = $result->fetch_assoc()) {
                            echo '
                            <tr>
                              <td>' . $counter . '</td>
                              <td>' . $row['customer_name'] . '</td>
                              <td>' . $row['mob_no'] . '</td>
                              <td>' . $row['invoice_no'] . '</td>
                              <td>' . $row['invoice_date'] . '</td>
                              <td>' . $row['total_amount'] . '</td>
                            </tr>
                            <tr>
                                <td colspan="6">
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
                            
                            // Fetch sale details for the current sale record
                            $saleDetailsSql = "SELECT * FROM sale_details WHERE sale_id = " . $row['id'];
                            $saleDetailsResult = $conn->query($saleDetailsSql);

                            while ($saleDetailsRow = $saleDetailsResult->fetch_assoc()) {
                                echo '
                                    <tr>
                                        <td>' . getItemName($saleDetailsRow['item_id'], $conn) . '</td>
                                        <td>' . $saleDetailsRow['qty'] . '</td>
                                        <td>' . $saleDetailsRow['price'] . '</td>
                                        <td>' . $saleDetailsRow['amount'] . '</td>
                                    </tr>';
                            }

                            echo '
                                        </tbody>
                                    </table>
                                </td>
                            </tr>';
                            $counter++; // Increment counter
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
