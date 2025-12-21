<?php
// user/pay_action.php
session_start();
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$vehicleId = intval($_POST['vehicle_id'] ?? 0);
$method = trim($_POST['method'] ?? 'Cash');

if ($vehicleId <= 0) {
    echo json_encode(['status'=>'error','message'=>'Invalid vehicle id']);
    exit;
}

try {
    $conn->begin_transaction();

    // check if vehicle exists and has a fee set
    $stmt = $conn->prepare("SELECT fee, out_time FROM vehicles WHERE id = ? LIMIT 1 FOR UPDATE");
    $stmt->bind_param("i", $vehicleId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['status'=>'error','message'=>'Vehicle not found']);
        exit;
    }
    $row = $res->fetch_assoc();
    $fee = floatval($row['fee']);
    $outTime = $row['out_time'];

    if ($outTime === null) {
        // vehicle not exited yet — don't allow payment before exit
        $conn->rollback();
        echo json_encode(['status'=>'error','message'=>'Vehicle has not exited yet']);
        exit;
    }

    // check if payment already recorded for this vehicle
    $chk = $conn->prepare("SELECT id FROM payments WHERE vehicle_id = ? LIMIT 1");
    $chk->bind_param("i", $vehicleId);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        $conn->rollback();
        echo json_encode(['status'=>'error','message'=>'Payment already completed']);
        exit;
    }

    // insert payment
    $ins = $conn->prepare("INSERT INTO payments (vehicle_id, amount, payment_time, method) VALUES (?, ?, NOW(), ?)");
    $ins->bind_param("ids", $vehicleId, $fee, $method);
    $ins->execute();

    $conn->commit();

    echo json_encode([
        'status'=>'success',
        'message'=>'Payment successful. Total Paid: $' . number_format($fee,2),
        'vehicle_id' => $vehicleId,
        'amount' => $fee
    ]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("pay_action error: " . $e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error while processing payment']);
    exit;
}
?>
