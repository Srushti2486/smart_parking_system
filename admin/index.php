<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}
require_once '../config.php';

// Count slots
$totalSlots = $conn->query("SELECT COUNT(*) AS cnt FROM slots")->fetch_assoc()['cnt'];
$occupied = $conn->query("SELECT COUNT(*) AS cnt FROM slots WHERE status='occupied'")->fetch_assoc()['cnt'];
$free = $totalSlots - $occupied;

// Total payments
$totalPayments = $conn->query("SELECT SUM(amount) AS total FROM payments")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Smart Parking</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #0d0d14;
    color: #e4e4e7;
    margin: 0;
    padding: 0;
}

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
    color: #ffd24d;
    font-size: 20px;
    font-weight: 600;
}

.nav {
    display: flex;
    gap: 20px;
}

.nav-link {
    color: #e4e4e7;
    text-decoration: none;
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
}

/* ===== Content ===== */
.content {
    max-width: 1100px;
    margin: 50px auto;
    padding: 0 20px;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    color: #ffd24d;
    font-size: 30px;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.card {
    background: #15151f;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.06);
    padding: 24px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
}

.card h3 {
    color: #ffcc00;
    margin-bottom: 15px;
}

.card p {
    font-size: 24px;
    font-weight: bold;
    color: #fff;
}

/* Quick links */
.quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.quick-links a {
    display: block;
    padding: 18px;
    border-radius: 12px;
    background: #1f1f2e;
    color: #ffd24d;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
}

.quick-links a:hover {
    background: #ffd24d;
    color: #1f1f2e;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
    .quick-links {
        grid-template-columns: 1fr;
    }
    .nav {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<header class="app-header">
    <div class="brand">🚗 Smart Parking Admin</div>
    <nav class="nav">
        <a href="index.php" class="nav-link active">Dashboard</a>
        <a href="slots.php" class="nav-link">Slots</a>
        <a href="vehicles.php" class="nav-link">Vehicles</a>
        <a href="vip.php" class="nav-link">VIP</a>
        <a href="payments.php" class="nav-link">Payments</a>
        <a href="reports.php" class="nav-link">Reports</a>
        <a href="../logout.php" class="nav-link logout">Logout</a>
    </nav>
</header>

<!-- Main Content -->
<main class="content">
    <div class="page-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h1>
        <p>Overview of your parking system</p>
    </div>

    <!-- Dashboard Summary Cards -->
    <div class="dashboard-cards">
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
            <h3>Total Fees Collected</h3>
            <p>$<?php echo $totalPayments ?? 0; ?></p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="slots.php">Manage Slots</a>
        <a href="vehicles.php">All Vehicles</a>
        <a href="vip.php">VIP Management</a>
        <a href="payments.php">Payments</a>
        <a href="reports.php">Reports</a>
        <a href="../logout.php">Logout</a>
    </div>
</main>

</body>
</html>
