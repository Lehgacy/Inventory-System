<?php
include 'connection.php';

// Get customer ID safely
$customerId = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$customerId) {
    echo "Error: Customer ID not provided.";
    exit;
}

// Fetch customer info
$sql = "SELECT * FROM customers WHERE customer_id = '$customerId'";
$result = mysqli_query($conn, $sql);

// Change the Echo Statement
if (!$result || mysqli_num_rows($result) === 0) {
    echo "Order successfully Deleted";
    exit;
}

$customer = mysqli_fetch_assoc($result);

// Fetch all items purchased by this customer
$itemsQuery = "SELECT * FROM customer_items WHERE customer_id = '$customerId'";
$itemsResult = mysqli_query($conn, $itemsQuery);

$totalQuery = "SELECT SUM(quantity * cost) AS total_price 
               FROM customer_items 
               WHERE customer_id = '$customerId'";
$totalResult = mysqli_query($conn, $totalQuery);
$totalData = mysqli_fetch_assoc($totalResult);
$totalPrice = $totalData['total_price'] ?? 0;

// Update the customers table (optional, keeps DB in sync)
mysqli_query($conn, "UPDATE customers SET total_price = '$totalPrice' WHERE customer_id = '$customerId'");

// Now display it
echo "<div class='customer-info'>";
echo "<p><strong>Company:</strong> " . htmlspecialchars($customer['company_name'] ?? 'Company name') . "</p>";
echo "<p><strong>Location:</strong> " . htmlspecialchars($customer['location'] ?? 'Location') . "</p>";
echo "<p><strong>Phone:</strong> " . htmlspecialchars($customer['Phone'] ?? 'Phone') . "</p>";
echo "<p><strong>Total Price:</strong> ₵" . number_format($totalPrice, 2) . "</p>";
echo "</div>";



    echo "<table>
            <tr>
              <th>ITEM NAME</th>
              <th>QUANTITY</th>
              <th>COST (GHS)</th>
              <th>TOTAL (GHS)</th>
              <th>ITEM CODE</th>
              <th>ORDER DATE</th>
               <th>ACTION</th>
            </tr>";

  while ($item = mysqli_fetch_assoc($itemsResult)) {
    echo "<tr>
        <td>" . htmlspecialchars($item['item_name']) . "</td>
        <td>" . htmlspecialchars($item['quantity']) . "</td>
        <td>₵" . number_format($item['cost'], 2) . "</td>
        <td>₵" . number_format($item['quantity'] * $item['cost'], 2) . "</td>
        <td>" . htmlspecialchars($item['item_code']) . "</td>
        <td>" . htmlspecialchars($item['orderdate']) . "</td>
        <td>
          <button class='btn-update'
            data-id='" . htmlspecialchars($item['item_id']) . "'
            data-custid='" . htmlspecialchars($customer['customer_id']) . "'
            data-item='" . htmlspecialchars($item['item_name']) . "'
            data-qty='" . htmlspecialchars($item['quantity']) . "'
            data-price='" . htmlspecialchars($item['cost']) . "'>
            Update
          </button>
          <button class='btn-delete' 
            data-item-id='" . htmlspecialchars($item['item_id']) . "' 
            data-customer-id='" . htmlspecialchars($customer['customer_id']) . "'>
            Delete
          </button>
        </td>
      </tr>";
}

    echo "</table>";
    echo  "<br> <button class='receiptbtn' onclick='refreshOrders(this)'>🔄 Refresh</button>";



if (!isset($_POST['id'])) {
    echo "<p>No customer selected.</p>";
    exit;
}

$customer_id = $_POST['id'];
?>
