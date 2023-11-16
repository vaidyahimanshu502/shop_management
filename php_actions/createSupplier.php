<?php
include('../config/db_connect.php');

$oldSupplierName = '';
$oldSupplierContact = '';
$oldSupplierAddress = '';
$oldSupplierStatus = 'Available';  // Default value

if ($_POST) {
    $supplierName = $_POST['supplier_name'];
    $supplierContact = $_POST['mobile_no'];
    $supplierAddress = $_POST['address'];
    $supplierStatus = $_POST['status'] == "Available" ? 1 : 0;
    $currentDateTime = date('Y-m-d H:i:s');

    // Set old values for repopulating the form
    $oldSupplierName = $supplierName;
    $oldSupplierContact = $supplierContact;
    $oldSupplierAddress = $supplierAddress;
    $oldSupplierStatus = $_POST['status'];

    // Validate inputs
    if (empty($supplierName) || empty($supplierContact) || empty($supplierAddress) || empty($supplierStatus)) {
        echo '<script>alert("All fields are required!");</script>';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $supplierName)) {
        echo '<script>alert("Invalid supplier name. Please enter only letters and spaces!");</script>';
    } elseif (!preg_match('/^[0-9]{10}$/', $supplierContact)) {
        echo '<script>alert("Invalid mobile number. Please enter a valid 10-digit number!");</script>';
    } elseif (!preg_match('/^[a-zA-Z\s]+$/', $supplierAddress)) {
        echo '<script>alert("Invalid address. Please enter only letters and spaces!");</script>';
    } elseif ($supplierStatus != 0 && $supplierStatus != 1) {
        echo '<script>alert("Invalid supplier status!");</script>';
    } else {
        $sql = "INSERT INTO supplier (supplier_name, mobile_no, address, status, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $supplierName, $supplierContact, $supplierAddress, $supplierStatus, $currentDateTime);
        if ($stmt->execute()) {
            echo '<script>alert("Supplier created successfully.");</script>';
            header("location: ../supplier.php");
        } else {
            echo '<script>alert("Error in creating the supplier: ' . $stmt->error . '");</script>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop-Management | Create-Supplier</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include("../partials/header.php") ?>
     <div class="container my-2">
        <div class="row">
            <div class="col-md-10">
              <form action="" method="POST">
                <h1 class="text-center text-secondary">Suppliers's Creation form</h1>
                    <div class="mb-3">
                        <label for="supplierName" class="form-label">Item Name:</label>
                        <input type="text" class="form-control" name="supplier_name" id="supplierName" placeholder="Enter supplier's name..." value="<?= htmlspecialchars($oldSupplierName) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="mobile_no" class="form-label">Supplier Contact:</label>
                        <input type="text" class="form-control" name="mobile_no" placeholder="Enter supplier's mobile..." id="mobile_no" value="<?= htmlspecialchars($oldSupplierContact) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address:</label>
                        <input type="text" class="form-control" name="address" placeholder="Enter supplier's address..." id="address" value="<?= htmlspecialchars($oldSupplierAddress) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Select Status:</label>
                        <select class="form-select" id="status" name="status">
                            <option <?= $oldSupplierStatus === 'Available' ? 'selected' : '' ?>>Available</option>
                            <option <?= $oldSupplierStatus === 'Not Available' ? 'selected' : '' ?>>Not Available</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Create-Supplier</button>
              </form>
            </div>
        </div>
     </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
