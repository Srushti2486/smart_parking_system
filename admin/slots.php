<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit;
}
require_once '../config.php';

// Add slot
if(isset($_POST['slot_no'])){
    $slot_no = $_POST['slot_no'];
    $type = $_POST['type'];
    $conn->query("INSERT INTO slots (slot_no,type,status) VALUES ('$slot_no','$type','free')");
    $msg = "Slot Added Successfully!";
}

// Fetch slots
$slots = $conn->query("SELECT * FROM slots ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Slots | Admin</title>
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
.page-container { flex: 1; padding: 50px 8%; }

/* ======= FORM CARD ======= */
.form-card { background: rgba(31,31,46,0.85); border-radius: 14px; padding: 30px; max-width: 500px; margin: 0 auto 50px auto; box-shadow: 0 10px 25px rgba(0,0,0,0.6); }
.form-card h2 { color: #ffcc00; margin-bottom: 20px; text-align: center; }
.form-card input, .form-card select, .form-card button { width: 100%; padding: 12px 15px; margin-bottom: 15px; border-radius: 6px; border: none; font-size: 16px; }
.form-card input, .form-card select { background: rgba(255,255,255,0.05); color: #fff; }
.form-card input::placeholder { color: #aaa; }
.form-card button { background: linear-gradient(135deg, #ffcc00, #ff9900); color: #1f1f2e; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
.form-card button:hover { background: linear-gradient(135deg, #e6b800, #cc8500); color: #fff; }

/* ======= TABLE ======= */
table { width: 100%; border-collapse: collapse; background: rgba(31,31,46,0.85); border-radius: 10px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.6); }
th, td { padding: 12px 15px; text-align: center; }
th { background: #1f1f2e; color: #ffcc00; }
td { background: rgba(31,31,46,0.7); color: #fff; }
tr:hover td { background: rgba(255,204,0,0.1); }

/* ======= MESSAGE ======= */
.message { text-align: center; color: #ffcc00; margin-bottom: 20px; font-weight: 600; }

/* ======= RESPONSIVE ======= */
@media (max-width: 768px){
    .form-card { width: 90%; padding: 20px; }
    table th, table td { font-size: 14px; padding: 10px; }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar dark">
    <div class="logo">🚗 Smart Parking</div>
    <div class="nav-links">
        <a href="index.php" class="active">Dashboard</a>
        <a href="slots.php">Manage Slots</a>
        <a href="vehicles.php">All Vehicles</a>
        <a href="vip.php">VIP Management</a>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="page-container">

    <!-- Add Slot Form -->
    <div class="form-card">
        <h2>Add New Slot</h2>
        <?php if(isset($msg)) echo "<p class='message'>$msg</p>"; ?>
        <form method="POST">
            <input type="text" name="slot_no" placeholder="Slot Number" required>
            <select name="type">
                <option value="car">Car</option>
                <option value="bike">Bike</option>
                <option value="truck">Truck</option>
            </select>
            <button type="submit">Add Slot</button>
        </form>
    </div>

    <!-- Slots Table -->
    <h2 style="color:#ffcc00; text-align:center; margin-bottom:20px;">All Parking Slots</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Slot No</th>
            <th>Type</th>
            <th>Status</th>
        </tr>
        <?php while($row = $slots->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['slot_no']); ?></td>
            <td><?php echo ucfirst($row['type']); ?></td>
            <td><?php echo ucfirst($row['status']); ?></td>
        </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>
