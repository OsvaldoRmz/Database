<?php
/* === CONFIG / DEBUG === */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// TODO: fill these with your real values
const DB_HOST = 'mysql.hostinger.com';   // or 'localhost'
const DB_USER = 'u926109375_db_OsvaldoRmz';
const DB_PASS = 'Osvaldo10010028';
const DB_NAME = 'u926109375_Osvaldoramirez';

// node to summarize/plot (allow override via URL: ?node=node_2)
$chosenNode = isset($_GET['node']) ? $_GET['node'] : 'node_1';

/* === CONNECT === */
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
  exit("DB connection failed: " . htmlspecialchars($mysqli->connect_error));
}

/* === QUERIES === */
// Registered nodes
$reg_sql = "SELECT * FROM sensor_register ORDER BY node_name ASC";
$reg_res = $mysqli->query($reg_sql);
$registered = $reg_res ? $reg_res->fetch_all(MYSQLI_ASSOC) : [];

// Data received (sorted by node then time)
$data_sql = "SELECT node_name, time_received, temperature, humidity
             FROM sensor_data
             ORDER BY node_name ASC, time_received ASC";
$data_res = $mysqli->query($data_sql);
$received = $data_res ? $data_res->fetch_all(MYSQLI_ASSOC) : [];

// Averages for chosen node
$avg_stmt = $mysqli->prepare("SELECT AVG(temperature) AS avg_temp, AVG(humidity) AS avg_hum
                              FROM sensor_data WHERE node_name = ?");
$avg_stmt->bind_param('s', $chosenNode);
$avg_stmt->execute();
$avg_row  = $avg_stmt->get_result()->fetch_assoc();
$avgTemp  = isset($avg_row['avg_temp']) ? (float)$avg_row['avg_temp'] : null;
$avgHum   = isset($avg_row['avg_hum'])  ? (float)$avg_row['avg_hum']  : null;
$avg_stmt->close();

/* Data for chart: only the chosen node, ordered by time */
$chart_stmt = $mysqli->prepare("SELECT time_received, temperature
                                FROM sensor_data
                                WHERE node_name = ?
                                ORDER BY time_received ASC");
$chart_stmt->bind_param('s', $chosenNode);
$chart_stmt->execute();
$chart_rows = $chart_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$chart_stmt->close();

$mysqli->close();

/* Build arrays for Chart.js */
$chartLabels = array_map(fn($r) => $r['time_received'], $chart_rows);
$chartTemps  = array_map(fn($r) => (float)$r['temperature'], $chart_rows);
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
    .card { width: 750px; margin: 0 auto; background:#fff; border:1px solid #ddd; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.05); padding:22px 24px; }
    .grid { background-image: linear-gradient(var(--grid) 1px, transparent 1px),
                              linear-gradient(90deg, var(--grid) 1px, transparent 1px);
            background-size: 18px 18px; }
    h1 { margin:0 0 6px; text-align:center; font-weight:800; }
    .subtitle { text-align:center; color:#555; margin-bottom:12px; }
    .section { font-weight:700; margin:18px 0 8px; }
    table { width:100%; border-collapse:collapse; margin:6px 0 16px; }
    thead th { background: var(--accent); color:#fff; padding:10px 8px; text-align:left; }
    th, td { border:1px solid #cfd5db; padding:8px 10px; text-align:left; background:#fff; }
    .note { color:#333; font-size:14px; margin-top:6px; }
    #chart-container { margin-top: 10px; }
    canvas { max-width: 100%; }
    .node-picker { text-align:right; margin-bottom:8px; font-size:14px; }
    .node-picker a { color:#1b6; text-decoration:none; margin-left:8px; }
  </style>
</head>
<body>
  <div class="card grid">
    <h1>Welcome to SSU IoT Lab</h1>
    <div class="subtitle">Registered Sensor Nodes</div>

    <!-- Registered nodes -->
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
    <!-- Data received -->
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

    <!-- Averages for chosen node -->
    <div class="note">
      <?php if ($avgTemp !== null): ?>
        The Average Temperature for <strong><?= htmlspecialchars($chosenNode) ?></strong> has been:
        <strong><?= round($avgTemp, 2) ?></strong> °C
      <?php else: ?>
        No temperature data for <strong><?= htmlspecialchars($chosenNode) ?></strong>.
      <?php endif; ?>
      <br>
      <?php if ($avgHum !== null): ?>
        The Average Humidity for <strong><?= htmlspecialchars($chosenNode) ?></strong> has been:
        <strong><?= round($avgHum, 2) ?></strong> %
      <?php endif; ?>
    </div>

    <!-- Chart -->
    <div class="section">Temperature Plot</div>
    <div class="node-picker">Viewing: <strong><?= htmlspecialchars($chosenNode) ?></strong>
      <!-- quick links to switch node via URL -->
      <?php foreach ($registered as $r): ?>
        <a href="?node=<?= urlencode($r['node_name']) ?>"><?= htmlspecialchars($r['node_name']) ?></a>
      <?php endforeach; ?>
    </div>
    <div id="chart-container">
      <canvas id="mycanvas"></canvas>
    </div>
  </div>

  <!-- libs -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
  <script>
    // Data passed from PHP for the chosen node:
    const chartLabels = <?= json_encode($chartLabels) ?>;
    const chartTemps  = <?= json_encode($chartTemps)  ?>;
    const chosenNode  = <?= json_encode($chosenNode)  ?>;

    (function () {
      const ctx = document.getElementById('mycanvas').getContext('2d');
      new Chart(ctx, {
        type: 'bar', 
        data: {
          labels: chartLabels,
          datasets: [{
            label: 'Temperature',
            data: chartTemps.map(Number),
            backgroundColor: 'rgba(0, 128, 0, 0.30)',  // green
            borderColor:     'rgba(0, 128, 0, 0.90)',
            hoverBackgroundColor: 'rgba(0, 128, 0, 0.60)',
            hoverBorderColor:     'rgba(0, 128, 0, 1)',
            tension: 0.25,
            pointRadius: 2,
            fill: true
          }]
        },
        options: {
          plugins: {
            title: { display: true, text: `Sensor Node ${chosenNode}` }
          },
          scales: {
            x: { title: { display: true, text: 'Time' } },
            y: { title: { display: true, text: 'Temperature (°C)' } }
          }
        }
      });
    })();
  </script>
</body>
</html>
