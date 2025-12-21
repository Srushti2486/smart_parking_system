<?php
session_start();
require_once '../config.php';
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit;
}

// Stats
$totalSlots = $conn->query("SELECT COUNT(*) AS cnt FROM slots")->fetch_assoc()['cnt'];
$occupied = $conn->query("SELECT COUNT(*) AS cnt FROM slots WHERE status='occupied'")->fetch_assoc()['cnt'];
$free = $totalSlots - $occupied;
$totalVehicles = $conn->query("SELECT COUNT(*) AS cnt FROM vehicles")->fetch_assoc()['cnt'];
$totalVIP = $conn->query("SELECT COUNT(*) AS cnt FROM vehicles WHERE vip=1")->fetch_assoc()['cnt'];
$totalPayments = $conn->query("SELECT SUM(amount) AS total FROM payments")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports | Admin</title>
<style>
/* ======= GLOBAL ======= */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { background: #0d0d17; color: #f1f1f1; min-height: 100vh; display: flex; flex-direction: column; }

/* ======= NAVBAR ======= */
.navbar.dark { background: #1a1a2e; display: flex; justify-content: space-between; align-items: center; padding: 18px 5%; box-shadow: 0 2px 12px rgba(0,0,0,0.6); position: sticky; top: 0; z-index: 100; }
.navbar .logo { font-size: 22px; font-weight: 600; color: #ffcc00; }
.navbar .nav-links a { text-decoration: none; color: #ccc; margin-left: 25px; font-weight: 500; transition: color 0.3s ease; }
.navbar .nav-links a:hover, .navbar .nav-links a.active { color: #ffcc00; }
.logout-btn { background: linear-gradient(135deg, #ffcc00, #ff9900); color: #1f1f2e; padding: 6px 14px; border-radius: 6px; font-weight: 600; transition: all 0.3s ease; }
.logout-btn:hover { background: linear-gradient(135deg, #e6b800, #cc8500); color: #fff; }

/* ======= PAGE CONTAINER ======= */
.page-container { flex: 1; padding: 50px 8%; text-align: center; }

/* ======= CARDS ======= */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 35px;
  justify-content: center;
  align-items: stretch;
  max-width: 1100px;
  margin: 0 auto;
}

.card {
  background: rgba(31,31,46,0.85);
  border-radius: 14px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.6);
  padding: 30px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover { transform: translateY(-6px); box-shadow: 0 15px 35px rgba(0,0,0,0.8); }

.card h3 { color: #ffcc00; font-size: 20px; margin-bottom: 15px; }
.card p { font-size: 24px; font-weight: 600; color: #fff; }

/* ======= RESPONSIVE ======= */
@media (max-width: 768px){
  .cards-grid { grid-template-columns: 1fr; gap: 25px; }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar dark">
    <div class="logo">🚗 Smart Parking</div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="slots.php">Manage Slots</a>
        <a href="vehicles.php">All Vehicles</a>
        <a href="vip.php">VIP Management</a>
        <a href="payments.php">Payments</a>
        <a href="reports.php" class="active">Reports</a>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="page-container">
    <h2>Reports & Summary</h2>
    <div class="cards-grid">
        <div class="card">
            <h3>Total Slots</h3>
            <p><?php echo $totalSlots; ?></p>
        </div>
        <div class="card">
            <h3>Occupied Slots</h3>
            <p><?php echo $occupied; ?></p>
        </div>
        <div class="card">
            <h3>Free Slots</h3>
            <p><?php echo $free; ?></p>
        </div>
        <div class="card">
            <h3>Total Vehicles Parked</h3>
            <p><?php echo $totalVehicles; ?></p>
        </div>
        <div class="card">
            <h3>Total VIP Vehicles</h3>
            <p><?php echo $totalVIP; ?></p>
        </div>
        <div class="card">
            <h3>Total Payments Collected</h3>
            <p>$<?php echo $totalPayments ?? 0; ?></p>
        </div>
    </div>
</div>

</body>
</html>
