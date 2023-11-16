<?php
include('../config/db_connect.php');

// Initialize variables
$itemName = '';
$itemCode = '';
$price = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
    $itemCode = isset($_POST['item_code']) ? trim($_POST['item_code']) : '';
    $price = isset($_POST['price']) ? trim($_POST['price']) : '';
    $itemStatus = 1;
    $currentDateTime = date('Y-m-d H:i:s');

    // Validate inputs
    $validationPassed = true;
    $alertMessage = '';

    if (empty($itemName) || empty($itemCode) || empty($price)) {
        $validationPassed = false;
        $alertMessage = 'All fields are required!';
    } elseif (!is_numeric($price) || $price <= 0) {
        $validationPassed = false;
        $alertMessage = 'Invalid price. Please enter a valid positive number.';
    } elseif (!preg_match('/^[a-zA-Z]+$/', $itemName)) {
        $validationPassed = false;
        $alertMessage = 'Item name should only contain letters.';
    }

    if (!$validationPassed) {
        echo '<script>alert("' . $alertMessage . '");</script>';
    } else {
        // Check if the item with the given item code or item name already exists
        $checkSql = "SELECT * FROM items WHERE item_code = ? OR item_name = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $itemCode, $itemName);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo '<script>alert("Item with the provided item code or item name already exists!");</script>';
        } else {
            $checkStmt->close();

            // Proceed with inserting the item if it doesn't already exist
            $sql = "INSERT INTO items (item_name, item_code, price, status, created_at) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiss", $itemName, $itemCode, $price, $itemStatus, $currentDateTime);

            if ($stmt->execute()) {
                echo '<script>alert("Item created successfully.");</script>';
                header("location: ../index.php");
            } else {
                echo '<script>alert("Error in creating the item.' . $stmt->error . '");</script>';
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Create-Item</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("../partials/header.php") ?>
    <div class="container my-2">
        <div class="row">
            <div class="col-md-10">
                <form action="" method="POST">
                    <h1 class="text-center text-secondary">Item's Creation form</h1>
                    <div class="mb-3">
                        <label for="itemName" class="form-label">Item Name:</label>
                        <input type="text" value="<?php echo $itemName ?>" class="form-control" name="item_name" id="itemName" placeholder="Enter item's name..." required>
                    </div>
                    <div class="mb-3">
                        <label for="itemCode" class="form-label">Item Code:</label>
                        <input type="text" value="<?php echo $itemCode ?>" class="form-control" name="item_code" placeholder="Enter item's code..." id="itemCode" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="number" value="<?php echo $price ?>" class="form-control" name="price" placeholder="Enter item's price..." id="price" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create-Item</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
