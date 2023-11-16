<?php
// db connection
include('config/db_connect.php');

$sql = "SELECT * FROM items ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        $counter = 1; // Initialize counter
        while ($row = $result->fetch_assoc()) {
            echo '
                <tr>
                    <th scope="row">'. $counter .'</th>
                    <td>'. htmlspecialchars($row['item_name']) .'</td>
                    <td>'. htmlspecialchars($row['item_code']) .'</td>
                    <td>'. htmlspecialchars($row['price']) .'</td>
                    <td>
                        <a href="php_actions/editItem.php?id='. $row['id'] .'" class="btn btn-primary btn-sm">Edit</a>
                        <a href="./php_actions/deleteItem.php?id='. $row['id'] .'" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete this item?\')">Delete</a>
                    </td>
                </tr> 
            ';
            $counter++; // Increment counter for each row
        }
    } else {
        echo '<tr><td colspan="5">No items found.</td></tr>';
    }
} else {
    echo '<tr><td colspan="5">Error fetching items: ' . $conn->error . '</td></tr>';
}

// Close the database connection
$conn->close();
?>
