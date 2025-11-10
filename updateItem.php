<?php
include "connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get posted values safely
    $id       = $_POST['id'];
    $itemName = $_POST['item_name'];
    $qty      = $_POST['quantity'];
    $price    = $_POST['price'];

    // Validate fields
    if (empty($id) || empty($itemName) || $qty <= 0 || $price <= 0) {
        echo "Invalid input data.";
        exit;
    }

    // ✅ Check if the item exists
    $sql = "SELECT item_id FROM customer_items WHERE item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ✅ Update item info
        $update = "UPDATE customer_items 
                   SET item_name = ?, quantity = ?, cost = ? 
                   WHERE item_id = ?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("sidi", $itemName, $qty, $price, $id);
        $stmt2->execute();

        echo "Item record updated successfully.";
    } else {
        echo "Error: Item not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}

?>
