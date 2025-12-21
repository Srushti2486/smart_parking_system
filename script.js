// /smartparking/script.js
// Centralized JS for Park / Exit / Queue UI
// Assumes PHP endpoints under /smartparking/user/*.php

const API_BASE = '/smartparking/user'; // adjust only if your PHP endpoints are elsewhere
const QUEUE_URL = `${API_BASE}/queue_data.php`;
const PARK_URL  = `${API_BASE}/park_action.php`;
const EXIT_URL  = `${API_BASE}/exit_action.php`;
const PAY_URL   = `${API_BASE}/pay_action.php`;

/* -----------------------
   Small utility helpers
   ----------------------- */
function showMessage(msg, kind = 'info', targetId = 'message') {
  const el = document.getElementById(targetId);
  if (!el) return;
  el.innerText = msg;
  el.style.display = msg ? 'block' : 'none';
  // optionally add classes for styling based on `kind`
}

function safeText(s) {
  if (s === null || s === undefined) return '';
  return String(s);
}

/* -----------------------
   Queue & Stack UI
   ----------------------- */
function updateQueueUI(waitingQueue) {
  const queueDiv = document.getElementById('queueList');
  if (!queueDiv) return;
  queueDiv.innerHTML = '';
  if (!Array.isArray(waitingQueue) || waitingQueue.length === 0) {
    queueDiv.innerHTML = '<div>No vehicles in queue.</div>';
    return;
  }
  waitingQueue.forEach(item => {
    // item may be string like "ABC123 — 2025-11-20..."
    const row = document.createElement('div');
    row.className = 'queue-row';
    row.innerText = safeText(item);
    queueDiv.appendChild(row);
  });
}

function updateStackUI(exitStack) {
  const stackDiv = document.getElementById('stackList');
  if (!stackDiv) return;
  stackDiv.innerHTML = '';
  if (!Array.isArray(exitStack) || exitStack.length === 0) {
    stackDiv.innerHTML = '<div>No recent exits.</div>';
    return;
  }
  exitStack.forEach(item => {
    // item expected { car_no: "...", fee: "..." } or string fallback
    const row = document.createElement('div');
    row.className = 'stack-row';
    if (typeof item === 'object') {
      row.innerText = `${safeText(item.car_no)} — $${Number(item.fee).toFixed(2)}`;
    } else {
      row.innerText = safeText(item);
    }
    stackDiv.appendChild(row);
  });
}

/* Fetch queue & stack from server */
function updateVisuals() {
  fetch(QUEUE_URL, { cache: 'no-store' })
    .then(res => {
      if (!res.ok) throw new Error('Network response not ok: ' + res.status);
      return res.json();
    })
    .then(data => {
      // expected structure: { queue: [...], stack: [...] }
      // support older outputs where stack items have {car_no, fee}
      updateQueueUI(Array.isArray(data.queue) ? data.queue : []);
      updateStackUI(Array.isArray(data.stack) ? data.stack : []);
    })
    .catch(err => {
      console.error('updateVisuals error:', err);
      // optionally show a small message in the UI
      // showMessage('Unable to load queue/stack (check server)', 'error', 'message');
    });
}

/* -----------------------
   Park Vehicle
   ----------------------- */
function parkVehicle() {
  // Ensure element IDs match your park.php form
  const vehicle_no = (document.getElementById('vehicle_no') || {}).value;
  const vehicle_type = (document.getElementById('vehicle_type') || {}).value || 'car';
  const vip = (document.getElementById('vip') || {}).checked ? 1 : 0;

  if (!vehicle_no || vehicle_no.trim() === '') {
    alert('Enter vehicle number');
    return;
  }

  const fd = new FormData();
  fd.append('vehicle_no', vehicle_no.trim());
  fd.append('vehicle_type', vehicle_type);
  fd.append('vip', vip);

  fetch(PARK_URL, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      // show user-visible message
      showMessage(data.message || (data.status === 'queued' ? 'Added to queue' : 'Done'), 'info', 'message');

      // reset form if parked or queued
      const parkForm = document.getElementById('parkForm');
      if (parkForm && (data.status === 'success' || data.status === 'queued')) {
        parkForm.reset();
      }

      // refresh queue + stack from server
      updateVisuals();
    })
    .catch(err => {
      console.error('parkVehicle error:', err);
      showMessage('Server error while parking (check console)', 'error', 'message');
    });
}

/* -----------------------
   Exit Vehicle
   ----------------------- */
function exitVehicle() {
    const formData = new FormData();
    formData.append('vehicle_no', document.getElementById('vehicle_no').value);

    fetch('exit_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('message').innerText = data.message;

        if (data.status === 'success') {

            // Show fee box
            document.getElementById("paymentBox").style.display = "block";
            document.getElementById("feeAmount").innerText = data.fee;

            // Store fee + vehicle ID for barcode
            window.lastFee = data.fee;
            window.lastCar = document.getElementById('vehicle_no').value;

            document.getElementById('exitForm').reset();
        }
    })
    .catch(err => console.error(err));
}
function showBarcode() {
    let barcodeDiv = document.getElementById("barcodeArea");
    barcodeDiv.innerHTML = `
        <svg id="barcode"></svg>
        <p>Scan this code to complete payment</p>
    `;

    JsBarcode("#barcode", window.lastCar + "-" + window.lastFee, {
        format: "CODE128",
        width: 2,
        height: 80,
        displayValue: true
    });
}



/* -----------------------
   Payment
   ----------------------- */
function renderPaymentSection(vehicleId, fee) {
  let container = document.getElementById('paymentSection');
  if (!container) {
    // If the page doesn't have a designated div, create and append under first .card
    container = document.createElement('div');
    container.id = 'paymentSection';
    const firstCard = document.querySelector('.card');
    if (firstCard) firstCard.appendChild(container);
    else document.body.appendChild(container);
  }

  container.innerHTML = `
    <p style="margin:8px 0">Amount to pay: <strong>$${Number(fee).toFixed(2)}</strong></p>
    <button class="btn-primary" id="payNowBtn">Pay Now</button>
  `;

  document.getElementById('payNowBtn').onclick = function () {
    payNow(vehicleId);
  };
}

function payNow(vehicleId) {
  if (!vehicleId) { alert('Invalid vehicle id'); return; }

  const fd = new FormData();
  fd.append('vehicle_id', vehicleId);
  fd.append('method', 'Cash'); // change if you integrate a real gateway

  fetch(PAY_URL, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      alert(data.message || 'Payment complete');
      if (data.status === 'success') {
        // remove payment section
        const container = document.getElementById('paymentSection');
        if (container) container.remove();
        // refresh visuals so stack shows the paid exit
        updateVisuals();
      }
    })
    .catch(err => {
      console.error('payNow error:', err);
      alert('Payment failed (check console)');
    });
}

/* -----------------------
   Auto-refresh on load
   ----------------------- */
window.addEventListener('DOMContentLoaded', () => {
  // update visuals only if relevant container exists
  if (document.getElementById('queueList') || document.getElementById('stackList')) {
    updateVisuals();
    // refresh periodically
    setInterval(updateVisuals, 8000);
  }

  // expose functions globally so inline onclick in your pages work
  window.parkVehicle = parkVehicle;
  window.exitVehicle = exitVehicle;
  window.payNow = payNow;
  window.updateVisuals = updateVisuals;
});
