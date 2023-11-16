<?php
include('../config/db_connect.php');

$id = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Display a confirmation alert using JavaScript
    echo '<script>';
    echo 'if (confirm("Are you sure you want to delete this record?")) {';
    echo '    window.location.href = "delete_item.php?id=' . $id . '";';
    echo '} else {';
    echo '    window.location.href = "../index.php";';
    echo '}';
    echo '</script>';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $sql = "DELETE FROM items WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
    } catch (Exception $e) {
        echo "Error message: " . $e->getMessage();
    }

    header("location: ../index.php");
}
?>


