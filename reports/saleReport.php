<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Sale-Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .form-container {
            /* border: 3px solid gray;
            border-radius: 12px; */
        }

        .search-btn {
            margin-top: 3.5vh !important;
        }

        .navbar {
            border-bottom: 3px solid gray;
            border-top: 3px solid gray;
            background-color: lightgray;
            font-weight: 900;
        }
    </style>
</head>
<body>
    <!-- Nav-bar -->
    <?php include("../partials/header.php") ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="header">
                    <h1 class="text-center text-secondary display-3">
                        Date wise sale report
                    </h1>
                </div>
                <hr>

                <h3 class="text-center textprimary">Filter by date</h3>
                <div class="form-container">
                    <!-- Add a form for date range search -->
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="row m-3">
                        <div class="col-md-3 mb-3">
                            <label for="start_date">Start Date:</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_date">End Date:</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <button type="submit" class="btn btn-primary search-btn" name="search">Search</button>
                        </div>
                    </form>
                </div>

                <div class="content">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="row">S. No.</th>
                                <th scope="row">Invoice No.</th>
                                <th scope="row">Invoice Date</th>
                                <th scope="row">Customer Name</th>
                                <th scope="row">Amount</th>
                                <th scope="row">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // making db connection
                            include("../config/db_connect.php");

                            // Counter initialization
                            $counter = 1;

                            // Check connection
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }

                            // Initialize variables for the date range
                            $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
                            $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

                            // Check if the search button is clicked
                            $searchClicked = isset($_POST['search']);

                            // Define the appropriate SQL query based on whether the search button is clicked
                            $sql = $searchClicked
                                ? "SELECT id, invoice_no, invoice_date, total_amount, customer_name
                                FROM sale
                                WHERE invoice_date BETWEEN '$start_date' AND '$end_date'
                                ORDER BY invoice_date DESC"
                                : "SELECT id, invoice_no, invoice_date, total_amount, customer_name
                                FROM sale
                                ORDER BY invoice_date DESC";


                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $saleId = $row['id'];
                                    $invoiceNo = $row["invoice_no"];
                                    $invoiceDate = date("d-m-Y", strtotime($row["invoice_date"]));
                                    $amount = $row["total_amount"];
                                    $customerName = $row["customer_name"];

                                    // Output each row inside the loop
                                    echo "<tr>";
                                    echo "<td>" . $counter . "</td>";
                                    echo "<td>" . $invoiceNo . "</td>";
                                    echo "<td>" . $invoiceDate . "</td>";
                                    echo "<td>" . $customerName . "</td>";
                                    echo "<td>" . $amount . "</td>";
                                    echo "<td><a href='./views/detailedSaleReport.php?id=" . $saleId . "' class='btn btn-primary btn-sm'>View</a></td>";
                                    echo "</tr>";

                                    // Increment the counter
                                    $counter++;
                                }
                            } else {
                                echo "0 results";
                            }

                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
