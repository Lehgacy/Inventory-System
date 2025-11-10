<?php
session_start();
include("connection.php");

$username = "";
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT username FROM registrationtable WHERE email='$email'");
    if ($row = mysqli_fetch_assoc($query)) {
        $username = $row['username'];
    }}

  // ====== ADD OR UPDATE STOCK ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $item_name = trim($_POST['item_name']);
    $quantity = floatval($_POST['quantity']);
    $cost_price = floatval($_POST['cost_price']);
    $selling_price = floatval($_POST['selling_price']);
    $profit_margin = $selling_price - $cost_price;

    if ($item_id > 0) {
        // UPDATE existing record
        $stmt = $conn->prepare("UPDATE stocks SET item_name=?, quantity=?, cost_price=?, selling_price=?, profit_margin=? WHERE id=?");
        $stmt->bind_param("sidddi", $item_name, $quantity, $cost_price, $selling_price, $profit_margin, $item_id);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Stock updated successfully!');</script>";
    } else {
        // INSERT new record
        $stmt = $conn->prepare("INSERT INTO stocks (item_name, quantity, cost_price, selling_price, profit_margin, stock_date)
                                VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sidds", $item_name, $quantity, $cost_price, $selling_price, $profit_margin);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('New stock added successfully!');</script>";
    }

    header("Location: stocks.php");
    exit;
}

// ====== DELETE STOCK ======
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM stocks WHERE id = $id");
    header("Location: stocks.php");
    exit;
}

// Fetch all stock records
$stocks = [];
$totalQuantity = $totalCost = $totalSell = $totalProfit = 0;

$query = "SELECT * FROM stocks ORDER BY id DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stocks[] = $row;
        $totalQuantity += $row['quantity'];
        $totalCost += $row['cost_price'] * $row['quantity'];
        $totalSell += $row['selling_price'] * $row['quantity'];
        $totalProfit += $row['profit_margin'] * $row['quantity'];
    }
}
// ====== FETCH ALL STOCKS ======
$stocks = $conn->query("SELECT * FROM stocks ORDER BY id DESC");





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
     <script src="Homepage.js"></script>

     <link rel="stylesheet" href="stocks.css">
     <link rel="stylesheet" href="Icons/all.css">
     <link rel="stylesheet" href="Icons/fontawesome.min.css">
     

    <title>Stock</title>

</head>
<body>

<div class="toggle-btn" id="toggle-btn">
  <i class="fas fa-bars"></i>
</div>

<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar" id="sidebar">
  <ul>
    <li><a href="homepage.php" class="<?php echo ($current_page == 'homepage.php') ? 'active' : ''; ?>">
      <i class="fas fa-home"></i> Dashboard</a>
    </li>

    <li><a href="orders.php" class="<?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">
      <i class="fas fa-shopping-cart"></i> Orders</a>
    </li>

    <li><a href="transactions.php" class="<?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">
      <i class="fas fa-credit-card"></i> Transactions</a>
    </li>

    <!-- ✅ Fixed the active check here -->
    <li><a href="stocks.php" class="<?php echo ($current_page == 'stocks.php') ? 'active' : ''; ?>">
      <i class="fa-solid fa-grip"></i> Stock</a>
    </li>

    <li><a href="charts.php" class="<?php echo ($current_page == 'charts.php') ? 'active' : ''; ?>">
      <i class="fa-solid fa-chart-line"></i> Charts</a>
    </li>

    <li><a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
      <i class="fas fa-user"></i> Profile</a>
    </li>

    <li class="logout"><a href="logout.php">
      <i class="fas fa-sign-out-alt"></i> Logout</a>
    </li>
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
  
<div class="container">

  <!-- 🧾 Section Title -->
  <h2>Stock Management</h2>
  <form method="POST" id="stockForm">
    <input type="hidden" name="item_id" id="item_id">
    <input type="text" name="item_name" id="item_name" placeholder="Item Name" required>
    <input type="number" name="quantity" id="quantity" placeholder="Quantity" min="1" required>
    <input type="number" name="cost_price" id="cost_price" placeholder="Cost Price (₵)" step="0.01" required>
    <input type="number" name="selling_price" id="selling_price" placeholder="Selling Price (₵)" step="0.01" required>
    <input type="text" id="profit" placeholder="Profit (₵)" readonly>
    <button type="submit" id="addStock">Add Stock</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>ITEM NAME</th>
        <th>QUANTITY</th>
        <th>COST PRICE (₵)</th>
        <th>SELLING PRICE (₵)</th>
        <th>PROFIT (₵)</th>
        <th>DATE ADDED</th>
        <th>ACTION</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $stocks->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['item_name']) ?></td>
        <td><?= $row['quantity'] ?></td>
        <td><?= number_format($row['cost_price'], 2) ?></td>
        <td><?= number_format($row['selling_price'], 2) ?></td>
        <td style="color:green;">₵<?= number_format($row['profit_margin'], 2) ?></td>
        <td><?= $row['stock_date'] ?></td>
        <td>
          <button class="edit-btn"
            data-id="<?= $row['id'] ?>"
            data-name="<?= htmlspecialchars($row['item_name']) ?>"
            data-qty="<?= $row['quantity'] ?>"
            data-cost="<?= $row['cost_price'] ?>"
            data-sell="<?= $row['selling_price'] ?>"
          ><i class="fa fa-edit"></i></button>
          <button class="delete-btn" onclick="deleteStock(<?= $row['id'] ?>)"><i class="fa fa-trash"></i></button>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  
    <!-- 📈 Summary Totals -->
    <tfoot>
      <tr>
        <td>Total</td>
        <td><?= $totalQuantity; ?></td>
        <td>₵<?= number_format($totalCost, 2); ?></td>
        <td>₵<?= number_format($totalSell, 2); ?></td>
        <td>₵<?= number_format($totalProfit, 2); ?></td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
  </table>

</div>

<!-- ================================
     ⚙️ JAVASCRIPT SECTION
================================ -->
<script>
  // 📁 Sidebar Toggle
  document.getElementById("toggle-btn").addEventListener("click", function() {
    document.getElementById("sidebar").classList.toggle("active");
  });

// ===== Auto-calculate Profit =====
const costInput = document.getElementById('cost_price');
const sellInput = document.getElementById('selling_price');
const profitInput = document.getElementById('profit');

function calcProfit() {
  const cost = parseFloat(costInput.value) || 0;
  const sell = parseFloat(sellInput.value) || 0;
  profitInput.value = (sell - cost).toFixed(2);
}
costInput.addEventListener('input', calcProfit);
sellInput.addEventListener('input', calcProfit);

// ===== Edit Button Handler =====
document.querySelectorAll('.edit').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('item_id').value = btn.dataset.id;
    document.getElementById('item_name').value = btn.dataset.name;
    document.getElementById('quantity').value = btn.dataset.qty;
    document.getElementById('cost_price').value = btn.dataset.cost;
    document.getElementById('selling_price').value = btn.dataset.sell;
    calcProfit();
    document.getElementById('addStock').textContent = 'Update Stock';
  });
});

// ===== Delete Button =====
function deleteStock(id) {
  if (confirm("Are you sure you want to delete this stock?")) {
    window.location.href = "stocks.php?delete=" + id;
  }
}

</script> 


</body>
</html>