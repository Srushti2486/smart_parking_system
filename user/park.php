<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role'] != 'user'){
    header("Location: ../login.php");
    exit;
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Park Vehicle | Smart Parking</title>
<script src="http://localhost/smartparking/script.js"></script>

<style>
/* ===== Base Styling ===== */
body {
    background-color: #0e1017;
    font-family: "Poppins", sans-serif;
    color: #e0e0e0;
    margin: 0;
    padding: 0;
}

/* ===== Navbar ===== */
.navbar {
    background-color: #161b22;
    padding: 15px 50px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #2c2f36;
}
.navbar .logo {
    font-size: 1.5rem;
    font-weight: 600;
    color: #00bcd4;
}
.navbar .nav-links a {
    color: #c9d1d9;
    margin: 0 18px;
    text-decoration: none;
    transition: color 0.3s ease;
    font-weight: 500;
}
.navbar .nav-links a:hover,
.navbar .nav-links .active {
    color: #00bcd4;
}
.logout-btn {
    color: #f44336 !important;
    font-weight: 600;
}

/* ===== Main Layout ===== */
.main-container {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 40px;
    padding: 60px 6%;
    min-height: calc(100vh - 80px);
}
.left-panel, .right-panel {
    background: #1a1e27;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,0,0,0.6);
    padding: 40px 50px;
}

/* ===== Form Section ===== */
.left-panel h2 {
    color: #00bcd4;
    margin-bottom: 35px;
    font-size: 26px;
    text-align: center;
}

form label {
    display: block;
    margin-bottom: 10px;
    font-weight: 500;
    color: #bbb;
}
input[type="text"], select {
    width: 100%;
    padding: 14px 15px;
    margin-bottom: 25px;
    border: none;
    border-radius: 10px;
    background-color: #2a2d35;
    color: #fff;
    font-size: 15px;
    outline: none;
    transition: all 0.3s ease;
}
input[type="text"]:focus, select:focus {
    background-color: #32353f;
    box-shadow: 0 0 5px #00bcd4;
}
input[type="checkbox"] {
    transform: scale(1.2);
    margin-right: 8px;
}
button {
    width: 100%;
    padding: 14px;
    background-color: #00bcd4;
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}
button:hover {
    background-color: #0097a7;
}
#message {
    margin-top: 20px;
    text-align: center;
    font-weight: 500;
}

/* ===== Queue & Stack Section ===== */
.right-panel h3 {
    color: #00bcd4;
    text-align: center;
    margin-bottom: 20px;
    font-size: 22px;
}
.section-block {
    background: #242a36;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.4);
}
.section-block h4 {
    color: #ffcc00;
    text-align: center;
    margin-bottom: 10px;
}
.queue-container, .stack-container {
    background-color: #2f3542;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    color: #aaa;
    font-style: italic;
}
@media (max-width: 900px) {
    .main-container {
        grid-template-columns: 1fr;
        gap: 25px;
        padding: 30px 5%;
    }
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo">🚗 Smart Parking</div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="park.php" class="active">Park Vehicle</a>
        <a href="exit.php">Exit Vehicle</a>
        <a href="history.php">History</a>
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-container">

    <!-- Left: Parking Form -->
    <div class="left-panel">
        <h2>Park Your Vehicle</h2>
        <form id="parkForm">
            <label>Vehicle Number:</label>
            <input type="text" id="vehicle_no" name="vehicle_no" placeholder="Enter vehicle number" required>

            <label>Vehicle Type:</label>
            <select id="vehicle_type" name="vehicle_type">
                <option value="car">Car</option>
                <option value="bike">Bike</option>
                <option value="truck">Truck</option>
            </select>

            <label><input type="checkbox" id="vip" name="vip" value="1"> VIP Parking</label>

            <button type="button" onclick="parkVehicle()">Park Vehicle</button>
        </form>

        <div id="message"></div>
    </div>

    <!-- Right: Queue and Stack -->
    <div class="right-panel">
        <div class="section-block">
            <h3>🚦 Waiting Queue</h3>
            <div class="queue-container" id="queueList">No vehicles in queue.</div>
        </div>

        <div class="section-block">
            <h3>🅿️ Recently Exited Vehicles</h3>
            <div class="stack-container" id="stackList">No recent exits.</div>
        </div>
    </div>
</div>

<script>
// --- 1. Essential Visuals Update Function (from script.js) ---
// We assume updateVisuals(), updateQueueUI(), and updateStackUI() are in script.js
// but we call the main entry point here.

function parkVehicle(){
    const formData = new FormData();
    formData.append('vehicle_no', document.getElementById('vehicle_no').value);
    formData.append('vehicle_type', document.getElementById('vehicle_type').value);
    formData.append('vip', document.getElementById('vip').checked ? 1 : 0);

    fetch('park_action.php', {
        method:'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('message').innerText = data.message;
        
        // FIX 1: Check for both 'success' (parked) and 'queued' (waiting) status
        if(data.status === 'success' || data.status === 'queued'){
            document.getElementById('parkForm').reset();
            // Call the function from script.js to refresh the Queue/Stack panels
            if (typeof updateVisuals === 'function') {
                updateVisuals(); 
            } else {
                console.error("updateVisuals function not found. Did you link script.js correctly?");
            }
        }
    })
    .catch(err => console.error("Network or PHP Error:", err));
}

// FIX 2: Call the updateVisuals function when the page finishes loading
window.onload = function() {
    // This ensures the visuals are loaded immediately when the page opens
    if (document.getElementById('queueList') && typeof updateVisuals === 'function') {
        updateVisuals(); 
    }
};
// ... (The parkVehicle function you have or the corrected one above) ...

// **Ensure this runs when the page loads**
window.onload = function() {
    // Check if the required elements exist before calling the update function
    if (document.getElementById('queueList') && typeof updateVisuals === 'function') {
        updateVisuals(); 
    }
};
</script>

</body>
</html>


