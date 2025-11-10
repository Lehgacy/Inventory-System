<?php
session_start();
include 'connection.php';

if (isset($_POST['submitButton'])) {
    $totalPrice   = $_POST['total_price'];

    if (empty($totalPrice) || $totalPrice <= 0) {
        $_SESSION['alert'] = [
            'icon' => 'warning',
            'title' => 'Total Price is required!',
            'text' => 'Please add at least one item.',
            'timer' => '20'
        ];
        header("Location: homepage.php");
        exit;
    }

    $customerCode = "CUST" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

    // Insert into customers with date
    $sql = "INSERT INTO customers ( total_price, customer_code, orderdate) 
            VALUES ('$totalPrice', '$customerCode', NOW())";
    $query = mysqli_query($conn, $sql);

    if ($query) {
        $customerId = mysqli_insert_id($conn);

        if (!empty($_POST['item'])) {
            foreach ($_POST['item'] as $index => $itemName) {
                $qty   = $_POST['quantity'][$index];
                $cost  = $_POST['cost'][$index];
                $itemCode = "ITEM" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

                // Insert each item with date
                $sqlItem = "INSERT INTO customer_items (customer_id, item_name, quantity, cost, item_code, orderdate)
                            VALUES ('$customerId', '$itemName', '$qty', '$cost', '$itemCode', NOW())";
                mysqli_query($conn, $sqlItem);
            }
        }

        $_SESSION['alert'] = [
            'icon' => 'success',
            'title' => 'Orders Successfully Placed',
            'timer' => '2000'
        ];
    } else {
        $_SESSION['alert'] = [
            'icon' => 'error',
            'title' => 'Database Error',
            'text' => mysqli_error($conn)
        ];
    }

    header("Location: homepage.php");
    exit;
}


?>
