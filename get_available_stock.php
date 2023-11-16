<?php
include("./config/db_connect.php");

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $itemId = $_GET['id'];

    // Calculate purchased quantity
    $purchaseQuery = "SELECT COALESCE(SUM(`quantity`), 0) AS purchased_quantity FROM `purchase_details` WHERE `item_id` = ?";
    $purchaseStmt = $conn->prepare($purchaseQuery);
    $purchaseStmt->bind_param("i", $itemId);
    $purchaseStmt->execute();
    $purchasedQuantity = $purchaseStmt->get_result()->fetch_assoc()['purchased_quantity'];

    // Calculate sold quantity
    $saleQuery = "SELECT COALESCE(SUM(`qty`), 0) AS sold_quantity FROM `sale_details` WHERE `item_id` = ?";
    $saleStmt = $conn->prepare($saleQuery);
    $saleStmt->bind_param("i", $itemId);
    $saleStmt->execute();
    $soldQuantity = $saleStmt->get_result()->fetch_assoc()['sold_quantity'];

    // Calculate available stock
    $availableStock = $purchasedQuantity - $soldQuantity;

    // Return the result as JSON
    echo json_encode(['success' => true, 'available_stock' => $availableStock]);
} else {
    // Invalid request
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>