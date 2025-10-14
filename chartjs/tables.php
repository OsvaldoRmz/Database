<?php
/***** DEV: show errors while you’re building (remove later) *****/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/***** DB CONFIG — fill in with your actual credentials *****/
const DB_HOST = 'mysql.hostinger.com';   // or 'localhost'
const DB_USER = '';
const DB_PASS = '';
const DB_NAME = '';
/***** CONNECT *****/
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
  exit("DB connection failed: " . htmlspecialchars($mysqli->connect_error));
}

/***** QUERIES *****/
// Registered nodes (show all columns)
$reg_sql = "SELECT * FROM sensor_register ORDER BY node_name ASC";
$reg_res = $mysqli->query($reg_sql);
$registered = $reg_res ? $reg_res->fetch_all(MYSQLI_ASSOC) : [];

// Data received (sorted by Node then Time)
$data_sql = "
  SELECT node_name, time_received, temperature, humidity
  FROM sensor_data
  ORDER BY node_name ASC, time_received ASC
";
$data_res = $mysqli->query($data_sql);
$received = $data_res ? $data_res->fetch_all(MYSQLI_ASSOC) : [];

$mysqli->close();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Welcome to SSU IoT Lab</title>
  <style>
    :root { --accent:#8fc33a; --grid:#eef1f4; }
    * { box-sizing: border-box; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 28px; background:#fff; }
    .card { width: 720px; background:#fff; border:1px solid #dcdcdc; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.05); padding:22px 24px; margin:auto; }
    .grid { background-image: linear-gradient(var(--grid) 1px, transparent 1px), linear-gradient(90deg, var(--grid) 1px, transparent 1px); background-size: 18px 18px; }
    h1 { margin: 0 0 6px; font-weight: 800; text-align:center; }
    .subtitle { text-align:center; color:#555; margin-bottom:14px; }
    .section { font-weight:700; margin:18px 0 6px; text-align:left; }
    table { width:100%; border-collapse:collapse; margin:8px 0 20px; }
    thead th { background: var(--accent); color:#fff; padding:10px 8px; text-align:left; }
    td { border:1px solid #cfd5db; padding:8px 10px; background:#fff; }
    th { border:1px solid #a9c37a; }
    .note { color:#555; font-size: 14px; margin-top: 4px; }
  </style>
</head>
<body>
  <div class="card grid">
    <h1>Welcome to SSU IoT Lab</h1>
    <div class="subtitle">Registered Sensor Nodes</div>

    <!-- Registered Sensor Nodes (sensor_register) -->
    <table>
      <thead>
        <tr>
          <?php if (!empty($registered)): ?>
            <?php foreach (array_keys($registered[0]) as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
          <?php else: ?>
            <th>No rows in sensor_register</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($registered as $row): ?>
          <tr>
            <?php foreach ($row as $val): ?>
              <td><?= htmlspecialchars((string)$val) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="section">Data Received</div>
    <!-- Data Received (sensor_data) sorted by Node then Time -->
    <table>
      <thead>
        <tr>
          <?php if (!empty($received)): ?>
            <?php foreach (array_keys($received[0]) as $col): ?>
              <th><?= htmlspecialchars($col) ?></th>
            <?php endforeach; ?>
          <?php else: ?>
            <th>No rows in sensor_data</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($received as $row): ?>
          <tr>
            <?php foreach ($row as $val): ?>
              <td><?= htmlspecialchars((string)$val) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="note">Shown in required order: <strong>Node Name</strong> → <strong>Time</strong>.</div>
  </div>
</body>
</html>
