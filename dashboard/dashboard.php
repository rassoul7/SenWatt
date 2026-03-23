<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/database/config.php';

// Récupérer les dernières mesures par prise
$stmt = $pdo->prepare("
    SELECT prise_id, nom_appareil, puissance, energie_kwh, cout_fcfa, timestamp
    FROM consommation
    WHERE user_id = ?
    ORDER BY timestamp DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$mesures = $stmt->fetchAll();

// Récupérer les alertes récentes
$stmt2 = $pdo->prepare("
    SELECT * FROM alertes 
    WHERE user_id = ? 
    ORDER BY timestamp DESC 
    LIMIT 5
");
$stmt2->execute([$_SESSION['user_id']]);
$alertes = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>SenWatt — Dashboard</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; background: #f0f4f8; }
    .navbar { background: #1a1a2e; color: white; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; }
    .navbar h1 { font-size: 20px; }
    .navbar a { color: #f0f4f8; text-decoration: none; font-size: 14px; }
    .container { max-width: 1100px; margin: 32px auto; padding: 0 16px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px; }
    .stat-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .stat-card .label { font-size: 13px; color: #888; margin-bottom: 8px; }
    .stat-card .value { font-size: 28px; font-weight: bold; color: #1a1a2e; }
    .stat-card .unit { font-size: 13px; color: #888; }
    .section { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 24px; }
    .section h2 { font-size: 16px; color: #1a1a2e; margin-bottom: 16px; border-bottom: 2px solid #f0f4f8; padding-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { text-align: left; color: #888; font-weight: normal; padding: 8px 12px; border-bottom: 1px solid #f0f4f8; }
    td { padding: 10px 12px; border-bottom: 1px solid #f8f8f8; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .badge-on { background: #eaf3de; color: #3b6d11; }
    .badge-alert { background: #fcebeb; color: #a32d2d; }
    .badge-ok { background: #e1f5ee; color: #0f6e56; }
    .btn { padding: 6px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: bold; }
    .btn-off { background: #fcebeb; color: #a32d2d; }
    .btn-on { background: #eaf3de; color: #3b6d11; }
    .alerte-row { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0f4f8; font-size: 14px; }
    .alerte-icon { font-size: 18px; }
    .alerte-msg { color: #444; flex: 1; }
    .alerte-time { color: #aaa; font-size: 12px; }
  </style>
</head>
<body>
  <nav class="navbar">
    <h1>⚡ SenWatt</h1>
    <div>Bonjour, <?= htmlspecialchars($_SESSION['user_nom']) ?> &nbsp;|&nbsp; <a href="historique.php">Historique</a> &nbsp;|&nbsp; <a href="?logout=1">Déconnexion</a></div>
  </nav>

  <div class="container">
    <!-- Cartes statistiques -->
    <div class="grid">
      <div class="stat-card">
        <div class="label">Puissance totale actuelle</div>
        <div class="value"><?= array_sum(array_column($mesures, 'puissance')) ?> <span class="unit">W</span></div>
      </div>
      <div class="stat-card">
        <div class="label">Énergie consommée</div>
        <div class="value"><?= round(array_sum(array_column($mesures, 'energie_kwh')), 2) ?> <span class="unit">kWh</span></div>
      </div>
      <div class="stat-card">
        <div class="label">Coût estimé</div>
        <div class="value"><?= round(array_sum(array_column($mesures, 'cout_fcfa'))) ?> <span class="unit">FCFA</span></div>
      </div>
      <div class="stat-card">
        <div class="label">Alertes actives</div>
        <div class="value" style="color:#e24b4a"><?= count($alertes) ?></div>
      </div>
    </div>

    <!-- Tableau des prises -->
    <div class="section">
      <h2>🔌 État des prises en temps réel</h2>
      <table>
        <thead>
          <tr>
            <th>Appareil</th>
            <th>Prise</th>
            <th>Puissance</th>
            <th>Énergie</th>
            <th>Coût</th>
            <th>Dernière mesure</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mesures as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['nom_appareil']) ?></td>
            <td><span class="badge badge-on"><?= htmlspecialchars($m['prise_id']) ?></span></td>
            <td><strong><?= $m['puissance'] ?> W</strong></td>
            <td><?= $m['energie_kwh'] ?> kWh</td>
            <td><?= $m['cout_fcfa'] ?> FCFA</td>
            <td style="color:#888;font-size:13px"><?= $m['timestamp'] ?></td>
            <td>
              <button class="btn btn-off" onclick="piloter('<?= $m['prise_id'] ?>', 'OFF')">Éteindre</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Alertes -->
    <div class="section">
      <h2>🚨 Alertes récentes</h2>
      <?php if (empty($alertes)): ?>
        <p style="color:#888;font-size:14px">Aucune alerte pour le moment ✅</p>
      <?php else: ?>
        <?php foreach ($alertes as $a): ?>
        <div class="alerte-row">
          <span class="alerte-icon">⚠️</span>
          <span class="alerte-msg">
            <strong><?= htmlspecialchars($a['type_alerte']) ?></strong> — <?= htmlspecialchars($a['message']) ?>
          </span>
          <span class="alerte-time"><?= $a['timestamp'] ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <script>
    function piloter(prise_id, etat) {
      fetch('http://localhost:1880/api/commande', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prise_id: prise_id, etat: etat })
      })
      .then(r => r.json())
      .then(data => alert('Commande envoyée : ' + prise_id + ' → ' + etat))
      .catch(err => alert('Node-RED pas encore connecté — commande simulée'));
    }

    // Rafraîchir toutes les 30 secondes
    setTimeout(() => location.reload(), 30000);
  </script>
</body>
</html>
<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>