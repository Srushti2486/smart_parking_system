<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

require_once '../config.php';

// FETCH ALL SLOTS FROM DATABASE
$data = $conn->query("SELECT * FROM slots");
$slots = [];
while ($row = $data->fetch_assoc()) {
    $slots[] = $row;
}

// BUBBLE SORT (Sort by Slot Number)
function bubbleSortSlots(&$arr) {
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j]['slot_no'] > $arr[$j + 1]['slot_no']) {
                $temp       = $arr[$j];
                $arr[$j]    = $arr[$j + 1];
                $arr[$j + 1]= $temp;
            }
        }
    }
}

// SELECTION SORT (Sort by status: free → occupied)
function selectionSortSlots(&$arr) {
    $n = count($arr);
    for ($i = 0; $i < $n - 1; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j]['status'] < $arr[$min]['status']) {
                $min = $j;
            }
        }
        $temp      = $arr[$min];
        $arr[$min] = $arr[$i];
        $arr[$i]   = $temp;
    }
}

// ---- APPLY SORTING BASED ON QUERY STRING (GET) ----
$algo = $_GET['algo'] ?? null;

if ($algo === 'bubble') {
    bubbleSortSlots($slots);
} elseif ($algo === 'selection') {
    selectionSortSlots($slots);
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Sort Slots | Smart Parking</title>
<style>
body{
    background:#0d0d15;
    color:#fff;
    font-family:Poppins, Arial, sans-serif;
    padding:30px;
}
.card{
    background:#1b1b27;
    padding:20px;
    border-radius:10px;
    max-width:800px;
    margin:auto;
}
button{
    padding:10px 20px;
    background:#ffd24d;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:600;
    margin-right:10px;
}
button:hover{
    background:#ffbe25;
}
table{
    width:100%;
    margin-top:20px;
    border-collapse:collapse;
}
th,td{
    padding:12px;
    border:1px solid #333;
    text-align:center;
}
th{
    background:#333;
}
.info{
    margin-top:10px;
    font-size:14px;
    color:#ccc;
}
</style>
</head>
<body>

<div class="card">
  <h2>Sort Parking Slots (Data Structure Demo)</h2>

  <!-- USE GET INSTEAD OF POST -->
  <form method="get">
    <button type="submit" name="algo" value="bubble">
        Bubble Sort (By Slot Number)
    </button>
    <button type="submit" name="algo" value="selection">
        Selection Sort (By Status)
    </button>
  </form>

  <div class="info">
    Current sorting:
    <strong>
      <?php
        if ($algo === 'bubble')      echo 'Bubble Sort by Slot Number';
        elseif ($algo === 'selection') echo 'Selection Sort by Status';
        else                          echo 'Original Order (from DB)';
      ?>
    </strong>
  </div>

  <table>
    <tr>
      <th>Slot No</th>
      <th>Type</th>
      <th>Status</th>
    </tr>

    <?php foreach ($slots as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['slot_no']) ?></td>
      <td><?= htmlspecialchars($s['type']) ?></td>
      <td><?= htmlspecialchars($s['status']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

</body>
</html>
