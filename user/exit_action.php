<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$carNo = $_POST['vehicle_no'] ?? '';
if (empty($carNo)) {
    echo json_encode(['status'=>'error','message'=>'Vehicle number required']);
    exit;
}

// 1️⃣ Fetch currently parked vehicle
$stmt = $conn->prepare("
    SELECT id, slot_id, user_id, in_time, vip 
    FROM vehicles 
    WHERE car_no=? AND out_time IS NULL
");
$stmt->bind_param("s", $carNo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status'=>'error','message'=>'Vehicle not found or already exited']);
    exit;
}

$vehicle = $result->fetch_assoc();

$vehicleId = $vehicle['id'];
$slotId    = $vehicle['slot_id'];
$userId    = $vehicle['user_id'];

// 2️⃣ Calculate fee
$inTime = new DateTime($vehicle['in_time']);
$now    = new DateTime();
$diff   = $inTime->diff($now);

$hours = max(1, ($diff->days * 24) + $diff->h);
$rate  = ($vehicle['vip'] == 1) ? 20 : 10;
$fee   = $hours * $rate;

// 3️⃣ Update vehicle record (exit)
$stmt = $conn->prepare("
    UPDATE vehicles 
    SET out_time = NOW(), fee=?, payment_status='PENDING' 
    WHERE id=?
");
$stmt->bind_param("di", $fee, $vehicleId);
$stmt->execute();

// 4️⃣ Free the slot
$conn->query("UPDATE slots SET status='free' WHERE id=$slotId");

// ==================================================================
// 5️⃣ AUTO–ASSIGN QUEUE → PARK FIRST WAITING VEHICLE
// ==================================================================

$assignCount = 0;

while (true) {

    // Get first waiting car (FIFO)
    $waiting = $conn->query("
        SELECT * FROM waiting_queue ORDER BY request_time ASC LIMIT 1
    ");
    if ($waiting->num_rows == 0) break;

    $nextCar = $waiting->fetch_assoc();
    $queueId = $nextCar['id'];

    // Find a free slot
    $slotQ = $conn->query("
        SELECT id FROM slots WHERE status='free' LIMIT 1
    ");
    if ($slotQ->num_rows == 0) break;

    $freeSlotId = $slotQ->fetch_assoc()['id'];

    // Park waiting vehicle
    $stmt = $conn->prepare("
        INSERT INTO vehicles (user_id, car_no, slot_id, in_time, vip, payment_status)
        VALUES (?, ?, ?, NOW(), ?, 'PENDING')
    ");
    $stmt->bind_param("isii",
        $nextCar['user_id'],
        $nextCar['car_no'],
        $freeSlotId,
        $nextCar['vip']
    );
    $stmt->execute();

    // Occupy slot
    $conn->query("UPDATE slots SET status='occupied' WHERE id=$freeSlotId");

    // Remove from waiting queue
    $conn->query("DELETE FROM waiting_queue WHERE id=$queueId");

    $assignCount++;
}

// ==================================================================
// 6️⃣ RESPONSE BACK TO FRONTEND
// ==================================================================

echo json_encode([
    'status'        => 'success',
    'message'       => "Vehicle exited. Fee: $$fee. Auto-assigned: $assignCount vehicles from queue.",
    'fee'           => $fee,
    'vehicle_id'    => $vehicleId,
    'slot_id'       => $slotId,
    'auto_assigned' => $assignCount
]);

exit;
?>
