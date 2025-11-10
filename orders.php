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
    WHERE MONTH(orderdate) = MONTH(CURDATE())
    AND YEAR(orderdate) = YEAR(CURDATE())
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
    WHERE MONTH(orderdate) = MONTH(CURDATE())
    AND YEAR(orderdate) = YEAR(CURDATE())
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
     <!-- SweetAlert2 -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

     <link rel="stylesheet" href="Order.css">
     <link rel="stylesheet" href="Icons/all.css">
     <link rel="stylesheet" href="Icons/fontawesome.min.css">
     
     

    <title>Order</title>

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

     <div class="order">
      <i class="fas fa-shopping-cart"></i>
     </div>
    <div class="cards">
      <div class="card">
        <p>No of Costomers</p>
        <h2><?php echo $totalCustomersMonth;?></h2>
      </div>

      <div class="card">
         <p>Monthly sales</p>
       <h2 id="totalSalesToday">₵<?php echo number_format($totalSalesMonth, 2); ?>
      </div>

      <div class="card">
        <p>Monthly sale completion</p>
        <h2><?php echo $totalCustomersMonth;?></h2>
      </div>
    </div>

   <form class="search-bar" onsubmit="return false;">
    <input type="text" placeholder="Customer ID or Item Code" id="searchInput">
    <button type="button">
    <i class='fa fa-search'></i>
    </button>
    </form>


      <!-- Orders Table --> 
   <h3>Orders</h3>
<?php
// Get today's date
$today = date('Y-m-d');

// Check if any rows exist for today
$hasTodayOrders = false;
while ($row = $result->fetch_assoc()) {
    // Compare order date with today's date
    if (date('Y-m-d', strtotime($row['orderdate'])) === $today) {
        $ordersToday[] = $row;
        $hasTodayOrders = true;
    }
}
?>

<?php if ($hasTodayOrders): ?>
  <table id="customerTable">
    <thead>
      <tr>
        <th>Customer ID</th>
        <th>Item Name</th>
        <th>Cost (GHS)</th>
        <th>Quantity</th>
        <th>Item Code</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($ordersToday as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
          <td><?php echo htmlspecialchars($row['item_name']); ?></td>
          <td>₵<?php echo number_format($row['cost'], 2); ?></td>
          <td><?php echo htmlspecialchars($row['quantity']); ?></td>
          <td><?php echo htmlspecialchars($row['item_code']); ?></td>
          <td><?php echo htmlspecialchars($row['orderdate']); ?></td>
          <td class="status complete">Complete</td>
          <td>
            <button class="action-btn btn-view" data-id="<?php echo htmlspecialchars($row['customer_id']); ?>">View</button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p style="text-align:center; font-weight:bold; margin-top:20px;">
    📅 No orders available for today.
  </p>
<?php endif; ?>

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
    // When View button clicked
  // When clicking "View", load customer’s order details
$(document).on("click", ".btn-view", function() {
    let customerId = $(this).data("id");
    
    // Save customer ID in the modal for later use
    $("#orderModal").attr("data-customer-id", customerId);

    $.ajax({
        url: "fetchOrderDetails.php",
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


$(document).on("click", ".btn-delete", function() {
    let itemId = $(this).data("item-id");
    let customerId = $(this).data("customer-id");

    if (!itemId || !customerId) {
        Swal.fire("Error", "Missing item or customer ID.", "error");
        console.log("Item ID:", itemId, "Customer ID:", customerId);
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "This will delete the item. If it's the only one, the customer's record will also be deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "deleteItem.php",
                type: "POST",
                data: { item_id: itemId, customer_id: customerId },
                success: function(response) {
                    Swal.fire("Done!", response, "success");
                    refreshOrders(); // optional refresh
                },
                error: function() {
                    Swal.fire("Error", "Failed to delete item.", "error");
                }
            });
        }
    });
});



// Handle Update
$(document).on("click", ".btn-update", function() {
    var itemId   = $(this).data("id");
    var itemName = $(this).data("item");
    var qty      = $(this).data("qty");
    var price    = $(this).data("price");

    Swal.fire({
        title: 'Update Order',
        html:
            '<input id="itemName" class="swal2-input" placeholder="Item Name" value="'+(itemName || '')+'">' +
            '<input id="quantity" type="number" class="swal2-input" placeholder="Quantity" value="'+(qty || '')+'">' +
            '<input id="price" type="number" step="0.01" class="swal2-input" placeholder="Price" value="'+(price || '')+'">',
        focusConfirm: false,
        confirmButtonText: 'Update',
        showCancelButton: true,
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const item_name = document.getElementById('itemName').value.trim();
            const quantity  = document.getElementById('quantity').value.trim();
            const price     = document.getElementById('price').value.trim();

            // ✅ Validate before sending
            if (!item_name || quantity <= 0 || price <= 0) {
                Swal.showValidationMessage('Please fill all fields correctly before updating.');
                return false;
            }

            return {
                id: itemId,
                item_name,
                quantity,
                price
            };
        }
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            $.ajax({
                url: "updateItem.php",
                type: "POST",
                data: result.value,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // ✅ Reload the updated order details
                    $(".btn-view[data-id='"+itemId+"']").trigger("click");
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed!',
                        text: 'Something went wrong while updating the record.'
                    });
                }
            });
        }
    });
});


function refreshOrders(button) {
    let customerId = $("#orderModal").attr("data-customer-id");

    if (!customerId) {
        Swal.fire({
            icon: "warning",
            title: "No Customer Selected",
            text: "Please open a customer's details before refreshing.",
            timer: 2500,
            showConfirmButton: false
        });
        return;
    }

    // SweetAlert loading indicator
    Swal.fire({
        title: "Refreshing...",
        text: "Fetching latest order details.",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "fetchOrderDetails.php",
        type: "POST",
        data: { id: customerId },
        success: function(response) {
            $("#orderDetails").html(response);

            // Success alert after refresh
            Swal.fire({
                icon: "success",
                title: "Refreshed!",
                text: "Order details updated successfully.",
                timer: 1800,
                showConfirmButton: false
            });
        },
        error: function() {
            Swal.fire({
                icon: "error",
                title: "Refresh Failed",
                text: "Could not refresh order details. Please check your connection."
            });
        }
    });
}

// Fetch and update dashboard Cards(For Monthly Sales,Number of customers,Daily Sales)
function refreshDashboard() {
  $.ajax({
    url: "fetchDashboardStats.php",
    type: "GET",
    dataType: "json",
    success: function(data) {
      // Update all four cards
      $("#totalSalesMonth").text("₵" + data.totalSalesMonth);
      $("#totalCustomersMonth").text(data.totalCustomersMonth);
    },
    error: function() {
      console.error("Error updating dashboard stats.");
    }
  });
}

// Auto-refresh every 30 seconds
setInterval(refreshDashboard, 30000);

// Also refresh right after the page loads
document.addEventListener("DOMContentLoaded", refreshDashboard);

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

</script>



</script>



    
</body>
</html>