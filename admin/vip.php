<?php
session_start();
require_once '../config.php';
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit;
}

// Mark vehicle as VIP
if(isset($_POST['vehicle_id'])){
    $vid = $_POST['vehicle_id'];
    $conn->query("UPDATE vehicles SET vip=1 WHERE id=$vid");
    $msg = "Vehicle marked VIP!";
}

$vehicles = $conn->query("SELECT id, car_no, vip FROM vehicles ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VIP Management | Admin</title>
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

/* ======= TABLE ======= */
table { width: 100%; border-collapse: collapse; background: rgba(31,31,46,0.85); border-radius: 10px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.6); margin-top: 30px; }
th, td { padding: 12px 15px; text-align: center; }
th { background: #1f1f2e; color: #ffcc00; }
td { background: rgba(31,31,46,0.7); color: #fff; }
tr:hover td { background: rgba(255,204,0,0.1); }

button.make-vip {
    background: linear-gradient(135deg, #ffcc00, #ff9900);
    color: #1f1f2e;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

button.make-vip:hover {
    background: linear-gradient(135deg, #e6b800, #cc8500);
    color: #fff;
}

h2 { color: #ffcc00; text-align: center; margin-bottom: 20px; }
.msg { text-align: center; color: #00ff88; margin-bottom: 20px; font-weight: bold; }

/* ======= RESPONSIVE ======= */
@media (max-width: 768px){
    table th, table td { font-size: 14px; padding: 10px; }
    .page-container { padding: 40px 5%; }
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
        <a href="vip.php" class="active">VIP Management</a>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="page-container">
    <h2>VIP Management</h2>
    <?php if(isset($msg)) echo "<p class='msg'>$msg</p>"; ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Car No</th>
            <th>VIP</th>
            <th>Action</th>
        </tr>
        <?php while($v=$vehicles->fetch_assoc()){ ?>
        <tr>
            <td><?php echo $v['id']; ?></td>
            <td><?php echo htmlspecialchars($v['car_no']); ?></td>
            <td><?php echo $v['vip'] ? "Yes" : "No"; ?></td>
            <td>
                <?php if(!$v['vip']){ ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="vehicle_id" value="<?php echo $v['id']; ?>">
                    <button type="submit" class="make-vip">Make VIP</button>
                </form>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
