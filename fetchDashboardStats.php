<?php
include "connection.php";

// 🟢 DAILY SALES
$sqlRevenueToday = "
    SELECT SUM(quantity * cost) AS total_sales_today
    FROM customer_items
    WHERE DATE(orderdate) = CURDATE()
";
$resultToday = mysqli_query($conn, $sqlRevenueToday);
$rowToday = mysqli_fetch_assoc($resultToday);
$totalSalesToday = $rowToday ? (float)$rowToday['total_sales_today'] : 0;


// 🟢 MONTHLY SALES
$sqlRevenueMonth = "
    SELECT SUM(quantity * cost) AS total_sales_month
    FROM customer_items
    WHERE MONTH(orderdate) = MONTH(CURDATE())
";
$resultMonth = mysqli_query($conn, $sqlRevenueMonth);
$rowMonth = mysqli_fetch_assoc($resultMonth);
$totalSalesMonth = $rowMonth ? (float)$rowMonth['total_sales_month'] : 0;

// 🟢 DAILY CUSTOMERS
$sqlCustomersToday = "
    SELECT COUNT(DISTINCT customer_id) AS total_customers_today
    FROM customers
    WHERE DATE(orderdate) = CURDATE()
";
$resultCustToday = mysqli_query($conn, $sqlCustomersToday);
$rowCustToday = mysqli_fetch_assoc($resultCustToday);
$totalCustomersToday = $rowCustToday ? (int)$rowCustToday['total_customers_today'] : 0;

// 🟢 MONTHLY CUSTOMERS
$sqlCustomersMonth = "
    SELECT COUNT(DISTINCT customer_id) AS total_customers_month
    FROM customers
    WHERE MONTH(orderdate) = MONTH(CURDATE())
";
$resultCustMonth = mysqli_query($conn, $sqlCustomersMonth);
$rowCustMonth = mysqli_fetch_assoc($resultCustMonth);
$totalCustomersMonth = $rowCustMonth ? (int)$rowCustMonth['total_customers_month'] : 0;

// 🟢 Return everything as JSON
echo json_encode([
    'totalSalesToday' => number_format($totalSalesToday, 2),
    'totalSalesMonth' => number_format($totalSalesMonth, 2),
    'totalCustomersToday' => $totalCustomersToday,
    'totalCustomersMonth' => $totalCustomersMonth
]);
?>
