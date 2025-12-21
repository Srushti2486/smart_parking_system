<?php
// smartparking/user/queue_data.php
session_start();
require_once '../config.php';
header('Content-Type: application/json');

$response = ['queue' => [], 'stack' => []];

// waiting queue (FIFO)
$q = $conn->query("SELECT car_no FROM waiting_queue ORDER BY request_time ASC");
if ($q) {
    while ($row = $q->fetch_assoc()) {
        $response['queue'][] = $row['car_no'];
    }
}

// recently exited vehicles (LIFO) — show last 8
$s = $conn->query("SELECT car_no, fee, out_time FROM vehicles WHERE out_time IS NOT NULL ORDER BY out_time DESC LIMIT 8");
if ($s) {
    while ($r = $s->fetch_assoc()) {
        $response['stack'][] = [
            'car_no' => $r['car_no'],
            'fee' => $r['fee']
        ];
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>
