<?php
include "connection.php";

$customer_id = $_GET['customer_id'] ?? 0;

// Fetch customer info
$sql = "SELECT * FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Fetch purchased items
$sqlItems = "SELECT * FROM customer_items WHERE customer_id = ?";
$stmt2 = $conn->prepare($sqlItems);
$stmt2->bind_param("i", $customer_id);
$stmt2->execute();
$resultItems = $stmt2->get_result();

// Calculate total
$total = 0;
foreach ($resultItems as $item) {
    $total += $item['quantity'] * $item['cost'];
}

// Build HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt - <?php echo htmlspecialchars($customer['customer_name']); ?></title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    padding: 20px;
    background: #fff;
}
h2 {
    text-align: center;
}
.receipt-container {
    width: 80%;
    margin: auto;
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 10px;
}
.receipt-header {
    text-align: center;
    margin-bottom: 20px;
}
.receipt-details p {
    margin: 5px 0;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}
th {
    background-color: #007bff;
    color: white;
}
.total {
    text-align: right;
    margin-top: 20px;
    font-size: 18px;
}
.print-btn {
    display: block;
    margin: 20px auto;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.print-btn:hover {
    background: #0056b3;
}

@media print {
    .print-btn {
        display: none;
    }
}


</style>
</head>
<body>

<div class="receipt-container">
    <div class="receipt-header">
        <h2>Customer Receipt</h2>
        <p><strong>Company:</strong> <?php echo htmlspecialchars($customer['company_name'] ?? 'N/A'); ?></p>
         <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone_number'] ?? 'N/A'); ?></p>
          <p><strong>Location:</strong> <?php echo htmlspecialchars($customer['location'] ?? 'N/A'); ?></p>

    </div>

    <table>
        <tr>
            <th>Item</th>
            <th>Qty</th>
            <th>Cost (₵)</th>
            <th>Total (₵)</th>
            <th>Date</th>
             <th>Item code</th>

        </tr>
        <?php
        foreach ($resultItems as $item) {
            echo "<tr>
                    <td>" . htmlspecialchars($item['item_name']) . "</td>
                    <td>" . htmlspecialchars($item['quantity']) . "</td>
                    <td>" . number_format($item['cost'], 2) . "</td>
                    <td>" . number_format($item['quantity'] * $item['cost'], 2) . "</td>
                    <td>" . htmlspecialchars($item['orderdate']) . "</td>
                    <td>" . htmlspecialchars($item['item_code']) . "</td>
                  </tr>";
        }
        ?>
    </table>

    <div class="total">
        <strong>Total Price: ₵<?php echo number_format($total, 2); ?></strong>
    </div>

    <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
</div>

</body>
</html>
