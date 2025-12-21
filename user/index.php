<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("Location: ../login.php");
    exit;
}
require_once '../config.php';
$username = $_SESSION['username'];

// --- Fetch dashboard stats from DB ---
$totalSlots = $conn->query("SELECT COUNT(*) AS cnt FROM slots")->fetch_assoc()['cnt'];
$occupied = $conn->query("SELECT COUNT(*) AS cnt FROM slots WHERE status='occupied'")->fetch_assoc()['cnt'];
$available = $totalSlots - $occupied;

// Count currently parked vehicles for this user
$userId = $_SESSION['user_id'];
$parked = $conn->query("SELECT COUNT(*) AS cnt FROM vehicles WHERE user_id=$userId AND out_time IS NULL")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html> 
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard | Smart Parking</title>
<style>
/* ======= GLOBAL ======= */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', 'Segoe UI', sans-serif; }
body { background: #0d0d17; color: #f1f1f1; min-height: 100vh; display: flex; flex-direction: column; }

/* ======= NAVBAR ======= */
.navbar.dark { background: #1a1a2e; display: flex; justify-content: space-between; align-items: center; padding: 18px 5%; box-shadow: 0 2px 12px rgba(0,0,0,0.6); position: sticky; top: 0; z-index: 100; }
.navbar .logo { font-size: 22px; font-weight: 600; color: #ffcc00; }
.navbar .nav-links a { text-decoration: none; color: #ccc; margin-left: 25px; font-weight: 500; transition: color 0.3s ease; }
.navbar .nav-links a:hover, .navbar .nav-links a.active { color: #ffcc00; }
.logout-btn { background: linear-gradient(135deg, #ffcc00, #ff9900); color: #1f1f2e; padding: 6px 14px; border-radius: 6px; font-weight: 600; transition: all 0.3s ease; }
.logout-btn:hover { background: linear-gradient(135deg, #e6b800, #cc8500); color: #fff; }

/* ======= DASHBOARD ======= */
.dashboard-container.dark { flex: 1; padding: 60px 8%; text-align: center; }
.dashboard-container.dark h1 { font-size: 34px; font-weight: 600; background: linear-gradient(90deg, #ffcc00, #ff9900); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.dashboard-container.dark .subtitle { margin-top: 10px; color: #aaa; font-size: 17px; margin-bottom: 60px; }

/* ======= CARDS ======= */
.dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 50px; justify-content: center; align-items: stretch; max-width: 1100px; margin: 0 auto; }
.card.dark-card { background: rgba(31, 31, 46, 0.85); border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.6); padding: 40px 30px; text-align: center; transition: all 0.3s ease; backdrop-filter: blur(6px); border: 1px solid rgba(255,255,255,0.05); }
.card.dark-card:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0,0,0,0.8); }
.card.dark-card h3 { font-size: 22px; color: #ffcc00; margin-bottom: 15px; }
.card.dark-card p { font-size: 24px; font-weight: 600; color: #fff; }
.btn-view { display: inline-block; margin-top: 20px; background: linear-gradient(135deg, #ffcc00, #ff9900); color: #1f1f2e; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; transition: all 0.3s ease; }
.btn-view:hover { background: linear-gradient(135deg, #e6b800, #cc8500); color: #fff; }



/* ======= FOOTER ======= */
footer { margin-top: 80px; text-align: center; padding: 20px; color: #777; background: #11111d; font-size: 14px; }

/* ======= RESPONSIVE ======= */
@media (max-width: 768px) {
  .navbar { flex-direction: column; text-align: center; gap: 10px; }
  .dashboard-container.dark { padding: 40px 20px; }
  .dashboard-cards { grid-template-columns: 1fr; gap: 25px; }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar dark">
  <div class="logo">🚗 Smart Parking</div>
  <div class="nav-links">
    <a href="index.php" class="active">Dashboard</a>
    <a href="park.php">Park Vehicle</a>
    <a href="exit.php">Exit Vehicle</a>
    <a href="history.php">History</a>
    <a href="../logout.php" class="logout-btn">Logout</a>
  </div>
</div>

<!-- Dashboard -->
<div class="dashboard-container dark">
  <h1>Welcome, <?php echo htmlspecialchars($username); ?> 👋</h1>
  <p class="subtitle">Your parking management at a glance</p>

  <div class="dashboard-cards">
    <div class="card dark-card">
      <h3>Available Slots</h3>
      <p id="availableCount"><?php echo $available; ?></p>
    </div>

    <div class="card dark-card">
      <h3>Parked Vehicles</h3>
      <p id="parkedCount"><?php echo $parked; ?></p>
    </div>

    <div class="card dark-card">
      <h3>Parking History</h3>
      <a href="history.php" class="btn-view">View History</a>
      <!-- Map Navigation Card -->
<div class="card dark-card">
  <h3>Sort Slots</h3>
  <a href="sort_slots.php" class="btn-view">Open Sort</a>
</div>



    </div>
  </div>
</div>


<footer>
  &copy; 2025 Smart Parking System | Designed by Pranusha
</footer>

</body>
</html>
