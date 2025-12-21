<?php
// smartparking/user/park_action.php
session_start();
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$carNo = trim($_POST['vehicle_no'] ?? '');
$type  = $_POST['vehicle_type'] ?? 'car';
$vip = (!empty($_POST['vip']) && $_POST['vip'] == "1") ? 1 : 0;

if ($carNo === '') {
    echo json_encode(['status'=>'error','message'=>'Vehicle number required']);
    exit;
}

/* 1) If vehicle already parked -> error */
$chk = $conn->prepare("SELECT id FROM vehicles WHERE car_no=? AND out_time IS NULL LIMIT 1");
$chk->bind_param("s", $carNo);
$chk->execute();
$chkRes = $chk->get_result();
if ($chkRes->num_rows > 0) {
    echo json_encode(['status'=>'error','message'=>'Vehicle already parked']);
    exit;
}

/* 2) Find first free slot */
$slotQ = $conn->query("SELECT id, slot_no FROM slots WHERE status='free' ORDER BY id ASC LIMIT 1");

if ($slotQ && $slotQ->num_rows > 0) {
    // free slot exists -> park now
    $slot = $slotQ->fetch_assoc();
    $slotId = intval($slot['id']);

    // If in waiting_queue, remove that row (user re-clicked Park)
    $delQ = $conn->prepare("DELETE FROM waiting_queue WHERE car_no = ? LIMIT 1");
    $delQ->bind_param("s", $carNo);
    $delQ->execute();

    // Insert into vehicles
    $ins = $conn->prepare("INSERT INTO vehicles (user_id, car_no, slot_id, in_time, vip) VALUES (?, ?, ?, NOW(), ?)");
    $ins->bind_param("isii", $userId, $carNo, $slotId, $vip);
    $ins->execute();

    // Mark slot occupied
    $upd = $conn->prepare("UPDATE slots SET status='occupied' WHERE id = ?");
    $upd->bind_param("i", $slotId);
    $upd->execute();

    echo json_encode([
        'status'=>'success',
        'message'=>"Vehicle parked in Slot " . $slot['slot_no'],
        'slot_no' => $slot['slot_no']
    ]);
    exit;
}

/* 3) No free slot -> add to waiting_queue (prevent duplicates) */
$chkW = $conn->prepare("SELECT id FROM waiting_queue WHERE car_no = ? LIMIT 1");
$chkW->bind_param("s", $carNo);
$chkW->execute();
$chkWRes = $chkW->get_result();
if ($chkWRes->num_rows > 0) {
    echo json_encode(['status'=>'queued','message'=>'Vehicle is already waiting in queue','car_no'=>$carNo]);
    exit;
}

// Insert into waiting_queue; column name is request_time (from your DESCRIBE)
$insQ = $conn->prepare("INSERT INTO waiting_queue (user_id, car_no, type, vip, request_time) VALUES (?, ?, ?, ?, NOW())");
$insQ->bind_param("issi", $userId, $carNo, $type, $vip);
$insQ->execute();

echo json_encode([
    'status'=>'queued',
    'message'=>'No free slots. Vehicle added to Waiting Queue.',
    'car_no'=>$carNo
]);
exit;
?>
