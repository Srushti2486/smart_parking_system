<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Exit Vehicle | Smart Parking</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>

<script src="http://localhost/smartparking/script.js"></script>
<style>

/* ===== Reset & Base ===== */
* { margin:0; padding:0; box-sizing:border-box; }

body {
  font-family:'Segoe UI',sans-serif;
  background:#0d0d14;
  color:#e4e4e7;
  min-height:100vh;
  padding-bottom:40px;
}

/* ===== Navbar ===== */
.app-header {
  background:#15151f;
  padding:20px 6%;
  display:flex;
  justify-content:space-between;
  align-items:center;
  box-shadow:0 2px 10px rgba(0,0,0,0.4);
  position:sticky;
  top:0;
  z-index:50;
}

.brand {
  color:#ffd24d;
  font-size:24px;
  font-weight:700;
  display:flex;
  gap:10px;
  align-items:center;
}

.nav {
  display:flex;
  gap:28px;
}

.nav-link {
  color:#e4e4e7;
  text-decoration:none;
  font-size:16px;
  font-weight:500;
  transition:0.2s ease;
}

.nav-link:hover,
.nav-link.active {
  color:#ffd24d;
  font-weight:600;
}

/* Logout Button */
.nav-link.logout {
  background:linear-gradient(135deg,#ff4545,#ff6f61);
  padding:10px 20px;
  border-radius:8px;
  font-weight:600;
  color:#fff;
  transition:0.2s;
}

.nav-link.logout:hover {
  transform:scale(1.05);
}

/* ===== Main Content ===== */
.content {
  max-width:900px;
  margin:50px auto;
  padding:0 20px;
}

/* Page Titles */
.page-header h2 {
  color:#ffd24d;
  font-size:36px;
  margin-bottom:5px;
}

.page-header .subtitle {
  color:#b3b3bd;
  font-size:17px;
}

/* ===== Card ===== */
.card {
  background:#15151f;
  border-radius:16px;
  padding:32px;
  margin-bottom:35px;
  border:1px solid rgba(255,255,255,0.06);
  box-shadow:0 5px 16px rgba(0,0,0,0.35);
}

/* ===== Form ===== */
.form-group {
  display:flex;
  flex-direction:column;
  margin-bottom:20px;
}

.form-group label {
  margin-bottom:6px;
  font-size:15px;
  font-weight:500;
}

.form-group input {
  padding:14px;
  background:#1f1f2b;
  border:1px solid #333;
  border-radius:10px;
  color:#fff;
  font-size:16px;
}

/* Buttons */
.btn-primary {
  padding:14px 28px;
  background:linear-gradient(135deg,#ffd24d,#ff9d42);
  border:none;
  color:#111;
  font-size:17px;
  font-weight:600;
  border-radius:12px;
  cursor:pointer;
  transition:0.25s ease;
}

.btn-primary:hover {
  transform:translateY(-2px);
  box-shadow:0 4px 12px rgba(255,200,60,0.25);
}

/* ===== Message Box ===== */
.message-box {
  margin-top:20px;
  padding:16px;
  background:rgba(255,210,77,0.12);
  border-left:4px solid #ffd24d;
  color:#ffd24d;
  border-radius:10px;
  font-size:16px;
}

/* ===== Queue & Stack Containers ===== */
.queue-container,
.stack-container {
  background:#1b1b27;
  padding:20px;
  border-radius:14px;
  min-height:80px;
  max-height:200px;
  overflow-y:auto;
  font-size:16px;
}

/* Individual Items */
.queue-container div,
.stack-container div {
  padding:12px 14px;
  background:#222233;
  margin-bottom:10px;
  border-radius:8px;
  font-size:15px;
  color:#e7e7e7;
}
</style>

</head>
<body>

<header class="app-header">
  <div class="brand">🚗 Smart Parking</div>
  <nav class="nav">
    <a href="index.php" class="nav-link">Dashboard</a>
    <a href="park.php" class="nav-link">Park Vehicle</a>
    <a href="exit.php" class="nav-link active">Exit Vehicle</a>
    <a href="history.php" class="nav-link">History</a>
    <a href="../logout.php" class="nav-link logout">Logout</a>
  </nav>
</header>

<main class="content">

  <div class="page-header">
    <h2>Exit Vehicle</h2>
  </div>

  <!-- EXIT FORM -->
  <section class="card">
    <h3>Exit Form</h3>
    <form id="exitForm">
      <div class="form-group">
        <label>Vehicle Number:</label>
        <input type="text" id="vehicle_no" required placeholder="Enter vehicle number">
      </div>
      <button type="button" class="btn-primary" onclick="exitVehicle()">Exit Vehicle</button>
    </form>

    <div id="message" class="message-box"></div>
    <div id="paymentSection"></div>
  </section>
 
<div id="barcodeBox" style="margin-top:20px; text-align:center; display:none;">
    <h3 style="color:#ffd24d;">Payment QR Code</h3>
    <canvas id="qrCodeCanvas"></canvas>
</div>




  <!-- QUEUE -->
  <section class="card">
    <h3>Waiting Queue</h3>
    <div id="queueList" class="queue-container">Loading...</div>
  </section>

  <!-- STACK -->
  <section class="card">
    <h3>Recently Exited Vehicles</h3>
    <div id="stackList" class="stack-container">Loading...</div>
  </section>

</main>

<script>
// Load queue and stack from PHP
function updateQueueStackFromServer() {
    fetch('queue_data.php')
    .then(res => res.json())
    .then(data => {
        let q = data.queue;
        let s = data.stack;

        document.getElementById("queueList").innerHTML =
            q.length ? q.map(v => `<div>${v}</div>`).join('') : "No vehicles in queue";

        document.getElementById("stackList").innerHTML =
            s.length ? s.map(v => `<div>${v.car_no} — Fee: $${v.fee}</div>`).join('') : "No recently exited vehicles";
    });
}

updateQueueStackFromServer();
setInterval(updateQueueStackFromServer, 8000);

// Exit vehicle + show pay button
function exitVehicle() {
    let vno = document.getElementById("vehicle_no").value.trim();
    if (!vno) return alert("Enter vehicle number");

    const fd = new FormData();
    fd.append("vehicle_no", vno);

    fetch("exit_action.php", { method:"POST", body:fd })
    .then(r => r.json())
    .then(data => {
        document.getElementById("message").innerText = data.message;

        if (data.status === "success") {
            document.getElementById("exitForm").reset();

            // Show payment button
            document.getElementById("paymentSection").innerHTML = `
                <p style="margin-top:10px;">Total Fee: <b>$${data.fee}</b></p>
                <button class="btn-primary" onclick="payNow(${data.vehicle_id})">
                    Pay Now
                </button>
            `;

            updateQueueStackFromServer();
        }
    })
    .catch(err => console.error(err));
}

// Pay Now
function payNow(vehicleId) {
    const fd = new FormData();
    fd.append("vehicle_id", vehicleId);
    fd.append("method", "Cash");

    fetch("pay_action.php", { method:"POST", body:fd })
    .then(r => r.json())
    .then(data => {

        if (data.status === "success") {

            // Show QR Code section
            document.getElementById("barcodeBox").style.display = "block";

            // Generate QR Code using fee + vehicle ID
            let qr = new QRious({
                element: document.getElementById("qrCodeCanvas"),
                size: 250,
                value: `Vehicle ID: ${vehicleId}\nPaid: $${data.amount}\nStatus: Paid`
            });

            // Clear payment section
            document.getElementById("paymentSection").innerHTML = "";

            updateQueueStackFromServer();

        } else {
            alert(data.message);
        }
    });
}


</script>

</body>
</html>
