<?php
include("../config/db_connect.php");

$id = '';
$supplierName = '';
$supplierContact = '';
$supplierAddress = '';
$supplierStatus = '';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
    $sql = "SELECT * FROM supplier WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();

        $supplierName = $row['supplier_name'];
        $supplierContact = $row['mobile_no'];
        $supplierAddress = $row['address'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($_POST) {
        $id = $_POST['supplier_id'];
        $supplierName = $_POST['supplier_name'];
        $supplierContact = $_POST['mobile_no'];
        $supplierAddress = $_POST['address'];
        $supplierStatus = 1;

        // Validate inputs
        if (empty($supplierName) || empty($supplierContact) || empty($supplierAddress)) {
            echo '<script>alert("All fields are required.");</script>';
        } elseif (!preg_match("/^[a-zA-Z0-9\s]+$/", $supplierName)) {
            echo '<script>alert("Invalid characters in Supplier Name.");</script>';
        } elseif (!preg_match("/^\d{10}$/", $supplierContact)) {
            echo '<script>alert("Invalid Supplier Contact number.");</script>';
        } elseif (!preg_match("/^[a-zA-Z0-9\s]+$/", $supplierAddress)) {
            echo '<script>alert("Invalid characters in Supplier Address.");</script>';
        } else {
            $sql = "UPDATE supplier SET supplier_name=?, mobile_no=?, address=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisi", $supplierName, $supplierContact, $supplierAddress, $id);
            if ($stmt->execute()) {
                echo '<script>alert("Supplier updated successfully.");</script>';
                header("location: ../supplier.php");
            } else {
                echo '<script>alert("Error updating the supplier: ' . $stmt->error . '");</script>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Edit-Supplier</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("../partials/header.php") ?>
    <div class="container my-2">
        <div class="row">
            <div class="col-md-10">
                <form action="" method="POST">
                    <h1 class="text-center text-secondary">Supplier's Updation form</h1>
                    <input type="hidden" name="supplier_id" value="<?php echo $id ?>">
                    <div class="mb-3">
                        <label for="supplierName" class="form-label">Supplier Name:</label>
                        <input type="text" class="form-control" name="supplier_name" id="supplierName" value="<?php echo $supplierName ?>" placeholder="Enter supplier's name..." required>
                    </div>
                    <div class="mb-3">
                        <label for="SupplierPhone" class="form-label">Supplier Contact:</label>
                        <input type="text" class="form-control" name="mobile_no" value="<?php echo $supplierContact ?>" placeholder="Enter supplier's contact..." required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address:</label>
                        <input type="text" class="form-control" name="address" value="<?php echo $supplierAddress ?>" placeholder="Enter supplier's address..." required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update-Supplier</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
