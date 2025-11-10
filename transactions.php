<?php
session_start();
include("connection.php");

$username = "";
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT username FROM registrationtable WHERE email='$email'");
    if ($row = mysqli_fetch_assoc($query)) {
        $username = $row['username'];
    }
}


$sqlCustomersMonth = "
    SELECT COUNT(DISTINCT customer_id) AS total_customers_month
    FROM customer_items
    WHERE YEAR(orderdate) = YEAR(CURDATE())
";


$resultCustomersMonth = mysqli_query($conn, $sqlCustomersMonth);
if ($resultCustomersMonth && mysqli_num_rows($resultCustomersMonth) > 0) {
    $row = mysqli_fetch_assoc($resultCustomersMonth);
    $totalCustomersMonth = (int)$row['total_customers_month'];
}

// Get Monthly Sales
$sqlRevenueMonth = "
    SELECT SUM(quantity * cost) AS total_sales_month
    FROM customer_items
    WHERE YEAR(orderdate) = YEAR(CURDATE())
";
$resultMonth = mysqli_query($conn, $sqlRevenueMonth);

if ($resultMonth && mysqli_num_rows($resultMonth) > 0) {
    $rowMonth = mysqli_fetch_assoc($resultMonth);
    $totalSalesMonth = (float)$rowMonth['total_sales_month'];
} else {
    $totalSalesMonth = 0;
}




// Fetch orders from your table
$sql = "SELECT customer_id, item_name, cost, quantity, item_code, orderdate
        FROM customer_items 
        ORDER BY orderdate DESC";
$result = $conn->query($sql);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
     <script src="Homepage/Homepage.js"></script>

     <link rel="stylesheet" href="Transaction.css">
     <link rel="stylesheet" href="Icons/all.css">
     <link rel="stylesheet" href="Icons/fontawesome.min.css">
     

    <title>Transaction</title>

</head>
<body>

<!-- Sidebar -->
<div class="toggle-btn" id="toggle-btn">
    <i class="fas fa-bars"></i>
  </div>

  <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
  <div class="sidebar" id="sidebar">

    <ul>
       <li><a href="homepage.php" class="<?php echo ($current_page == 'homepage.php') ? 'active' : ''; ?>">
      <i class="fas fa-home"></i> Dashboard</a></li>

      <li><a href="orders.php" class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
        <i class="fas fa-shopping-cart"></i>Orders</a></li>

      <li><a href="transactions.php" class="<?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">
        <i class="fas fa-credit-card"></i>Transactions</a></li>

      <li><a href="stocks.php" class="<?php echo ($current_page == 'stocks.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-grip"></i>Stock</a></li>

      <li><a a href="charts.php" class="<?php echo ($current_page == 'charts.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-line"></i>Charts</a></li>

      <li><a a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
        <i class="fas fa-user"></i>Profile</a></li>

      <li class="logout"><a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>Logout</a></li>
    </ul>
  </div>

  <!-- Main -->
  <div class="main">
    <!-- Navbar -->
    <div class="navbar">
      <div class="logo">
        <i class="fa-solid fa-circle-user"></i>
        <?php echo htmlspecialchars($username); ?>
      </div>
      <ul>
        <li><a href="logout.php" style="text-decoration:none; color:inherit;">Logout</a></li>
      </ul>
    </div>


    <!-- Cards -->
    <div class="cards">
      <div class="card">
        <p>No of Costomers</p>
        <h2><?php echo $totalCustomersMonth;?></h2>
      </div>

      <div class="card">
         <p>Yearly sales</p>
       <h2 id="totalSalesToday">₵<?php echo number_format($totalSalesMonth, 2); ?>
      </div>

      <div class="card">
        <p>Yearly sale completion</p>
        <h2><?php echo $totalCustomersMonth;?></h2>
      </div>
    </div>

    <!-- Search Bar -->
<form method="GET" action="" class="search-bar" onsubmit="return false;">
  <input type="text" placeholder="Customer ID or Item Code" id="searchInput">
  <button type="button"><i class='fa fa-search'></i></button>
</form>

<!-- Orders Table --> 
<table id="customerTable">
  <thead>
    <tr>
      <th>Customer_id</th>
      <th>Item_name</th>
      <th>Cost (GHS)</th>
      <th>Quantity</th>
      <th>Item_code</th>
      <th>Order Date</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="orderData">
    <!-- This part will be filled dynamically -->
  </tbody>
</table>


       <div class="modal-overlay" id="modalOverlay"></div>

<!-- Popup -->
<div class="modal" id="orderModal" >
  <div class="modal-header">
    <h3>Customer Purchases</h3>
    <span class="close-modal" id="closeModal">&times;</span>
  </div>
  <div class="modal-body" id="orderDetails">
    <!-- Order items will load here -->
  </div>
</div>



  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
$(document).ready(function() {
  // When clicking "View", load customer’s order details
$(document).on("click", ".btn-view", function() {
    let customerId = $(this).data("id");
    
    // Save customer ID in the modal for later use
    $("#orderModal").attr("data-customer-id", customerId);

    $.ajax({
        url: "fetchTransaction.php",
        type: "POST",
        data: { id: customerId },
        success: function(response) {
            $("#orderDetails").html(response);
            $("#orderModal").show(); // Show modal
        },
        error: function() {
            alert("Failed to fetch customer details.");
        }
    });
});


    // Close modal
    $("#closeModal, #modalOverlay").click(function() {
        $("#modalOverlay, #orderModal").fadeOut();
    });
});


//JavaScript for Search Functionality
document.getElementById("searchInput").addEventListener("keyup", function (e) {
  e.preventDefault();

  let filter = this.value.toLowerCase().trim();
  let table = document.getElementById("customerTable");
  let rows = table.getElementsByTagName("tr");

  // Loop through all rows except header
  for (let i = 1; i < rows.length; i++) {
    let customerIdCell = rows[i].getElementsByTagName("td")[0]; // Customer ID column
    let itemCodeCell   = rows[i].getElementsByTagName("td")[4]; // Item Code column

    let customerId = customerIdCell ? customerIdCell.textContent.toLowerCase() : "";
    let itemCode   = itemCodeCell ? itemCodeCell.textContent.toLowerCase() : "";

    // Show row if Customer ID or Item Code matches
    if (customerId.includes(filter) || itemCode.includes(filter)) {
      rows[i].style.display = "";
    } else {
      rows[i].style.display = "none";
    }
  }
});

$(document).ready(function(){
  function loadOrders(query = '') {
    $.ajax({
      url: 'searchOrders.php',
      type: 'GET',
      data: { query: query },
      success: function(data) {
        $('#orderData').html(data);
      }
    });
  }

  // Initial load (latest 5)
  loadOrders();

  // Search while typing
  $('#searchInput').on('keyup', function() {
    const query = $(this).val().trim();
    loadOrders(query); // works both for search and empty reset
  });
});


// Open a new window to show the receipt
function generateReceipt(customerId) {
    const receiptWindow = window.open('', '_blank', 'width=800,height=600');

    // Load the receipt HTML dynamically via AJAX
    fetch('generateReceipt.php?customer_id=' + customerId)
        .then(response => response.text())
        .then(html => {
            receiptWindow.document.open();
            receiptWindow.document.write(html);
            receiptWindow.document.close();
        })
        .catch(error => {
            alert("Error generating receipt: " + error);
        });
}
</script>


  



  </script>


   


    
</body>
</html>