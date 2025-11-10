<?php
include "connection.php";

$query = $_GET['query'] ?? '';

if ($query !== '') {
    // 🔍 Search mode: find any matching orders
    $sql = "SELECT * FROM customer_items 
            WHERE customer_id LIKE ? OR item_code LIKE ? 
            ORDER BY orderdate DESC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} else {
    // 🕐 Default: only show the latest 5 orders
    $sql = "SELECT * FROM customer_items ORDER BY orderdate DESC LIMIT 5";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['customer_id']}</td>
                <td>{$row['item_name']}</td>
                <td>₵" . number_format($row['cost'], 2) . "</td>
                <td>{$row['quantity']}</td>
                <td>{$row['item_code']}</td>
                <td>{$row['orderdate']}</td>
                <td class='status complete'>Complete</td>
                <td><button class='action-btn btn-view' data-id='{$row['customer_id']}'>View</button></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8' style='text-align:center;'>No matching records found.</td></tr>";
}

$stmt->close();
$conn->close();
?>
