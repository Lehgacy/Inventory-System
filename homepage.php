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


// --- Initialize variables to avoid "undefined variable" warnings ---
$totalCustomers = 0;
$totalSalesToday = 0.00;

// --- Get total sales for today ---
$sqlRevenue = "SELECT SUM(quantity * cost) AS total_sales_today
               FROM customer_items
               WHERE DATE(orderdate) = CURDATE()";
$result = mysqli_query($conn, $sqlRevenue);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $totalSalesToday = (float)($row['total_sales_today'] ?? 0);
}

// --- Get total customers for today ---
$sqlCustomers = "SELECT COUNT(*) AS total_customers 
                 FROM customers
                 WHERE DATE(orderdate) = CURDATE()";
$resultCustomers = mysqli_query($conn, $sqlCustomers);

if ($resultCustomers && mysqli_num_rows($resultCustomers) > 0) {
    $row = mysqli_fetch_assoc($resultCustomers);
    $totalCustomers = (int)($row['total_customers'] ?? 0);
}

}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
     <script src="Homepage.js"></script>

     <link rel="stylesheet" href="Homepage.css">
     <link rel="stylesheet" href="Icons/all.css">
     <link rel="stylesheet" href="Icons/fontawesome.min.css">
     

    <title>Dashboard</title>

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

      <li><a href="stocks.php" class="<?php echo ($current_page == 'bundles.php') ? 'active' : ''; ?>">
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
        <p>Number of Customers</p>
        <h2><?php echo $totalCustomers; ?></h2>
      </div>
      
      <div class="card">
        <p>Total Sales</p>
        <h2>₵<?php echo number_format($totalSalesToday , 2); ?></h2>
      </div>
      
      <div class="card">
        <p>Order Successful</p>
       <h2><?php echo $totalCustomers;?></h2>
      </div>
    </div>

    <!-- Chart -->
   <h2>Weekly Performance</h2>
  <div class="chart-container">
    <canvas id="lineChart"></canvas>
    </div>
  </div>

   <!--JAVA SCRIPT CODES FOR THE GRAPH-->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const ctx = document.getElementById("lineChart").getContext("2d");

      new Chart(ctx, {
        type: "line",
        data: {
          labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
          datasets: [{
            label: "Performance",
            data: [20, 50, 30, 70, 60, 90, 50],
            borderColor: "#4e2fff",
            backgroundColor: "rgba(78,47,255,0.1)",
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    });

     const toggleBtn = document.getElementById('toggle-btn');
     const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');

    });
  </script>

  

   <!-- Floating Open button -->
  <button id="openBtn" class="open-button" type="button" aria-label="Open form">Take Orders
    <i class="fas fa-shopping-cart" aria-hidden="true"></i>
  </button>

  <!-- Modal popup (hidden initially) -->
  <div id="popupForm" class="form-popup" role="dialog" aria-hidden="true" aria-modal="true" aria-labelledby="popupTitle">
   <form id="userForm" class="form-container" method="POST" action="SaveOrder.php" >


      <h2 id="popupTitle">Order Details</h2>

   <label>Price:</label>
   <input type="Float" name="total_price" id="totalPrice" class="price" readonly required  onfocus="this.removeAttribute('readonly'); this.blur();"><br><br>
  

  <!-- Button to open popup -->
  <button type="button" class="itembtn" id="openItemBtn">➕ Add Item</button>

  <!-- Items will show here -->
  <div class="item-list" id="itemList"></div>

  <br>
  <button type="submit" class="btn" name="submitButton">Submit Order</button>
</form>

  </div>

  


  <!-- Popup -->
<div class="popup-overlay" id="popupOverlay">
  <div class="popup">
    <h3>Add Item</h3>
    <label>Item:</label>
    <input type="text" id="popupItem" required>
    <label>Quantity:</label>
    <input type="number" id="popupQty" required>
    <label>Cost:</label>
    <input type="number" step="0.01" id="popupCost" required>

    <br><br>
    <button type="button" class="btn" id="saveItemBtn">Save Item</button>
    <button type="button" class="btn" id="closePopupBtn">Cancel</button>
  </div>
</div>

<script>

  document.addEventListener('DOMContentLoaded', () => {
  const openBtn = document.getElementById('openBtn');
  const popupForm    = document.getElementById('popupForm');

  // Open when "Take Order" button is clicked
  openBtn.addEventListener('click', () => {
    popupForm.style.display = 'flex';
    popupForm.setAttribute('aria-hidden', 'false');
  });

  // Close popup if you click outside the form
  popupForm.addEventListener('click', (e) => {
    if (e.target === popupForm) {
      popupForm.style.display = 'none';
      popupForm.setAttribute('aria-hidden', 'true');
    }
  });
});



document.addEventListener('DOMContentLoaded', () => {
  const openItemBtn   = document.getElementById('openItemBtn');
  const popupOverlay  = document.getElementById('popupOverlay');
  const closeItemBtn  = document.getElementById('closePopupBtn');
  const saveBtn       = document.getElementById('saveItemBtn');
  const itemList      = document.getElementById('itemList');
  const popupItem     = document.getElementById('popupItem');
  const popupQty      = document.getElementById('popupQty');
  const popupCost     = document.getElementById('popupCost');
  const totalPriceInput = document.getElementById('totalPrice');

  let totalPrice = 0;

  // Open popup form
  openItemBtn.addEventListener('click', () => {
    popupOverlay.style.display = 'flex';
  });

  // Close popup form
  closeItemBtn.addEventListener('click', () => {
    popupOverlay.style.display = 'none';

    popupItem.value = '';
    popupQty.value = '';    
    popupCost.value = '';
    
  });

  // Recalculate total price
  function updateTotal() {
    totalPrice = 0;
    const costInputs = itemList.querySelectorAll('input[name="cost[]"]');
    const qtyInputs  = itemList.querySelectorAll('input[name="quantity[]"]');

    costInputs.forEach((costInput, i) => {
      const cost = Number(costInput.value);
      const qty  = Number(qtyInputs[i].value);
      totalPrice += qty * cost;
    });

    totalPriceInput.value = totalPrice.toFixed(2); // show with 2 decimals
  }

  // Save item
  saveBtn.addEventListener('click', () => {
    const item = popupItem.value.trim();
    const qtyRaw = popupQty.value.trim();
    const costRaw = popupCost.value.trim();

    if (!item || !qtyRaw || !costRaw) {
      alert('Please fill all fields!');
      return;
    }

    const qty = Number(qtyRaw);
    const cost = Number(costRaw);

    if (!Number.isFinite(qty) || qty <= 0) {
      alert('Quantity must be positive');
      return;
    }
    if (!Number.isFinite(cost) || cost < 0) {
      alert('Cost must be 0 or more');
      return;
    }

    // Create row
    const row = document.createElement('div');
    row.className = 'item-row';
    row.textContent = `${item} - Qty: ${qty}, Cost: ${cost}`;

    // Hidden inputs
    function makeHidden(name, value) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      return input;
    }

    row.appendChild(makeHidden('item[]', item));
    row.appendChild(makeHidden('quantity[]', qty));
    row.appendChild(makeHidden('cost[]', cost));

    // Remove button
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.textContent = 'Remove';
    removeBtn.className = 'removeItemBtn';
    removeBtn.style.marginLeft = '10px';
    removeBtn.style.backgroundColor = '#ff4d4d';
    removeBtn.style.border = 'none';
    removeBtn.addEventListener('click', () => {
      row.remove();
      updateTotal(); // recalc after removing
    });
    row.appendChild(removeBtn);

    // Append row
    itemList.appendChild(row);

    // Clear fields & close popup
    popupItem.value = '';
    popupQty.value = '';
    popupCost.value = '';
    popupOverlay.style.display = 'none';

    // Update total
    updateTotal();
  });
});

// Function to refresh dashboard stats
function refreshDashboard() {
  $.ajax({
    url: "fetchDashboardStats.php",
    type: "GET",
    dataType: "json",
    success: function(data) {
      $("#totalSales").text("₵" + data.totalSalesToday);
      $("#totalCustomers").text(data.totalCustomers);

      Swal.fire({
        icon: 'success',
        title: 'Dashboard Updated!',
        text: 'Sales and customer count refreshed successfully.',
        timer: 1500,
        showConfirmButton: false
      });
    },
    error: function() {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Unable to refresh dashboard stats.'
      });
    }
  });
}




</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (isset($_SESSION['alert'])): ?>
<script>
Swal.fire({
  icon: '<?php echo $_SESSION['alert']['icon']; ?>',
  title: '<?php echo $_SESSION['alert']['title']; ?>',
  text: '<?php echo $_SESSION['alert']['text'] ?? ''; ?>'
});
</script>
<?php unset($_SESSION['alert']); endif; ?>


</body>
</html>