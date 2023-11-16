<?php
include("../config/db_connect.php");

// Validate the ID parameter
$id = '';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
} else {
    echo '<script>alert("Invalid or missing ID parameter.");</script>';
    header("location: ../supplier.php");
    exit;
}

// Check if the ID exists in the database
$checkSql = "SELECT * FROM supplier WHERE id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {
    echo '<script>alert("Record with the provided ID does not exist.");</script>';
    header("location: ../supplier.php");
    exit;
}

$checkStmt->close();

// Perform the deletion
$sql = "DELETE FROM supplier WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo '<script>alert("Record deleted successfully.");</script>';
} else {
    echo '<script>alert("Error deleting record: ' . $stmt->error . '");</script>';
}

$stmt->close();
header("location: ../supplier.php");
exit;
?>
