<?php
include 'connection.php';


if (isset($_POST['item_id']) && isset($_POST['customer_id'])) {
    $item_id = $_POST['item_id'];
    $customer_id = $_POST['customer_id'];

    // Count how many items this customer has
    $countQuery = "SELECT COUNT(*) AS total_items FROM customer_items WHERE customer_id = '$customer_id'";
    $result = mysqli_query($conn, $countQuery);
    $row = mysqli_fetch_assoc($result);
    $total_items = $row['total_items'];

    if ($total_items == 1) {
        // Delete all items and customer record
        mysqli_query($conn, "DELETE FROM customer_items WHERE customer_id = '$customer_id'");
        if (mysqli_query($conn, "DELETE FROM customers WHERE customer_id = '$customer_id'")) {
            echo "Customer and all items deleted successfully.";
        } else {
            echo "Error deleting customer: " . mysqli_error($conn);
        }
    } else {
        // Delete only the item
        if (mysqli_query($conn, "DELETE FROM customer_items WHERE item_id = '$item_id'")) {
            echo "Item deleted successfully.";
        } else {
            echo "Error deleting item: " . mysqli_error($conn);
        }
    }
} else {
    echo "Invalid request. Missing parameters.";
}


?>
