<?php 
include("../config/db_connect.php");

$id = '';
$itemName = '';
$itemCode = '';
$itemStatus = '';
$price = '';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
    $sql = "SELECT * FROM items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();

        $itemName = $row['item_name'];
        $itemCode = $row['item_code'];
        $price = $row['price'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $itemName = $_POST['item_name'];
        $itemCode = $_POST['item_code'];
        $price = $_POST['price'];
    }

    // Validate inputs
    if (empty($itemName) || empty($itemCode) || empty($price)) {
        echo '<script>alert("All fields are required.");</script>';
    } elseif (!is_numeric($price) || $price < 0) {
        echo '<script>alert("Invalid price. Please enter a valid positive number.");</script>';
    } else {
        try {
            $sql = "UPDATE items SET item_name = ?, item_code = ?, price = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $itemName, $itemCode, $price, $id);

            if ($stmt->execute()) {
                echo '<script>alert("Item updated successfully.");</script>';
                header("location: ../index.php");
            } else {
                echo '<script>alert("Error updating item: ' . $stmt->error . '");</script>';
            }
        } catch (mysqli_sql_exception $e) {
            echo '<script>alert("Error updating item: ' . $e->getMessage() . '");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Edit-Item</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("../partials/header.php") ?>
    <div class="container my-2">
        <div class="row">
            <div class="col-md-10">
                <form action="" method="POST">
                    <h1 class="text-center text-secondary">Item's Updation form</h1>
                    <input type="hidden" name="id" value="<?php echo $id ?>">
                    <div class="mb-3">
                        <label for="itemName" class="form-label">Item Name:</label>
                        <input type="text" class="form-control" name="item_name" id="itemName" value="<?php echo $itemName ?>" placeholder="Enter item's name..." required>
                    </div>
                    <div class="mb-3">
                        <label for="itemCode" class="form-label">Item Code:</label>
                        <input type="text" class="form-control" name="item_code" value="<?php echo $itemCode ?>" placeholder="Enter item's code..." id="itemCode" required>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="text" class="form-control" name="price" value="<?php echo $price ?>" placeholder="Enter item's price..." id="price" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update-Item</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
