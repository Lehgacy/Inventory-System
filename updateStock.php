<?php
include 'config.php'; // your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = intval($_POST['id']);
    $item  = mysqli_real_escape_string($conn, $_POST['item']);
    $qty   = floatval($_POST['qty']);
    $cost  = floatval($_POST['cost']);
    $sell  = floatval($_POST['sell']);

    // ✅ Calculate profit margin
    $profit = $sell - $cost;

    // ✅ Get current quantity
    $current = $conn->prepare("SELECT quantity FROM stocks WHERE id = ?");
    $current->bind_param("i", $id);
    $current->execute();
    $currentResult = $current->get_result();
    $row = $currentResult->fetch_assoc();
    $currentQty = $row ? $row['quantity'] : 0;

    // ✅ Add new quantity once
    $newQty = $currentQty + $qty;

    // ✅ Update with new values
    $update = $conn->prepare("UPDATE stocks 
        SET item_name = ?, 
            quantity = ?, 
            cost_price = ?, 
            selling_price = ?, 
            profit_margin = ?
        WHERE id = ?");
    $update->bind_param("sdddsi", $item, $newQty, $cost, $sell, $profit, $id);

    if ($update->execute()) {
        echo "Stock updated successfully!";
    } else {
        echo "Error updating stock: " . $conn->error;
    }

    $update->close();
    $conn->close();
}
?>
