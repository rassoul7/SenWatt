<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once '../database/config.php';

// Consommation par jour (7 derniers jours)
$stmt = $pdo->prepare("
    SELECT DATE(timestamp) as jour, 
           SUM(puissance) as total_w,
           SUM(energie_kwh) as total_kwh,
           SUM(cout_fcfa) as total_fcfa
    FROM consommation
    WHERE user_id = ?
    AND timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(timestamp)
    ORDER BY jour ASC
");
$stmt->execute([$_SESSION['user_id']]);
$historique = $stmt->fetchAll();

$jours = array_column($historique, 'jour');
$kwh   = array_column($historique, 'total_kwh');
$fcfa  = array_column($historique, 'total_fcfa');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>SenWatt — Historique</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: #f0f4f8; }
    .navbar { background: #1a1a2e; color: white; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; }
    .navbar h1 { font-size: 20px; }
    .navbar a { color: #f0f4f8; text-decoration: none; font-size: 14px; }
    .container { max-width: 900px; margin: 32px auto; padding: 0 16px; }
    .section { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 24px; }
    .section h2 { font-size: 16px; color: #1a1a2e; margin-bottom: 16px; }
    .total { font-size: 22px; font-weight: bold; color: #e85d04; margin-top: 16px; }
  </style>
</head>
<body>
  <nav class="navbar">
    <h1>⚡ SenWatt — Historique</h1>
    <div><a href="dashboard.php">← Retour dashboard</a></div>
  </nav>

  <div class="container">
    <div class="section">
      <h2>📊 Consommation journalière — 7 derniers jours</h2>
      <canvas id="chart" height="80"></canvas>
      <div class="total">
        Coût mensuel estimé : 
        <?= round(array_sum($fcfa) / max(count($fcfa), 1) * 30) ?> FCFA
      </div>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('chart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($jours) ?>,
        datasets: [
          {
            label: 'Énergie (kWh)',
            data: <?= json_encode($kwh) ?>,
            backgroundColor: 'rgba(232, 93, 4, 0.7)',
            borderRadius: 6
          },
          {
            label: 'Coût (FCFA)',
            data: <?= json_encode($fcfa) ?>,
            backgroundColor: 'rgba(26, 26, 46, 0.6)',
            borderRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
      }
    });
  </script>
</body>
</html>