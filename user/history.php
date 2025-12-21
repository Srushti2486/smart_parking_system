<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("Location: ../login.php");
    exit;
}
require_once '../config.php';
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parking History | Smart Parking</title>

<style>
/* ===== Base ===== */
body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #0d0d14;
  color: #e4e4e7;
  margin: 0;
  padding: 0;
}

a { text-decoration: none; }

/* ===== Navbar ===== */
.app-header {
  background: #15151f;
  padding: 18px 6%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.4);
  position: sticky;
  top: 0;
  z-index: 100;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #ffd24d;
  font-size: 20px;
  font-weight: 600;
}

.nav {
  display: flex;
  gap: 24px;
}

.nav-link {
  color: #e4e4e7;
  font-weight: 500;
  transition: color 0.2s ease;
}

.nav-link:hover, .nav-link.active {
  color: #ffd24d;
}

.nav-link.logout {
  background: linear-gradient(135deg, #ff4545, #ff6f61);
  color: #fff;
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  transition: transform 0.2s ease;
}

.nav-link.logout:hover { transform: translateY(-2px); }

/* ===== Content ===== */
.content {
  max-width: 1100px;
  margin: 50px auto;
  padding: 0 20px;
}

.page-header {
  text-align: center;
  margin-bottom: 30px;
}

.page-header h2 {
  font-size: 28px;
  color: #ffd24d;
}

.page-header p {
  font-size: 15px;
  color: #a5a5b1;
  margin-top: 6px;
}

/* ===== Card ===== */
.card {
  background: #15151f;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.06);
  padding: 24px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.3);
  overflow-x: auto;
}

/* ===== Table ===== */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 12px 15px;
  text-align: center;
}

th {
  background-color: #1f1f2e;
  color: #ffd24d;
  font-weight: 600;
}

td {
  background-color: #1b1b27;
  color: #e4e4e7;
  border-bottom: 1px solid #2a2a3a;
}

tr:hover td {
  background-color: #272736;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
  .nav { flex-wrap: wrap; justify-content: center; gap: 12px; }
  th, td { font-size: 13px; padding: 8px; }
}
</style>
</head>
<body>

<!-- Navbar -->
<header class="app-header">
  <div class="brand">🚗 Smart Parking</div>
  <nav class="nav">
    <a href="index.php" class="nav-link">Dashboard</a>
    <a href="park.php" class="nav-link">Park Vehicle</a>
    <a href="exit.php" class="nav-link">Exit Vehicle</a>
    <a href="history.php" class="nav-link active">History</a>
    <a href="../logout.php" class="nav-link logout">Logout</a>
  </nav>
</header>

<!-- Main Content -->
<main class="content">
  <div class="page-header">
    <h2>Parking History</h2>
    <p>Your parking records at a glance</p>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Vehicle No</th>
          <th>Slot</th>
          <th>In Time</th>
          <th>Out Time</th>
          <th>VIP</th>
          <th>Fee</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $userId = $_SESSION['user_id'];
        $q = $conn->query("SELECT v.car_no, s.slot_no, v.in_time, v.out_time, v.vip, v.fee
                           FROM vehicles v
                           JOIN slots s ON v.slot_id=s.id
                           WHERE v.user_id=$userId
                           ORDER BY v.in_time DESC");
        while($row = $q->fetch_assoc()){
            echo "<tr>
                <td>{$row['car_no']}</td>
                <td>{$row['slot_no']}</td>
                <td>{$row['in_time']}</td>
                <td>{$row['out_time']}</td>
                <td>".($row['vip']?"Yes":"No")."</td>
                <td>{$row['fee']}</td>
            </tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
