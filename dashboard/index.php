<?php
session_start();
require_once '../database/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Email ou mot de passe incorrect.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>SenWatt — Connexion</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f0f4f8; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); width: 360px; }
    h1 { color: #1a1a2e; margin-bottom: 8px; }
    p { color: #666; margin-bottom: 24px; font-size: 14px; }
    input { width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
    button { width: 100%; padding: 12px; background: #e85d04; color: white; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; font-weight: bold; }
    button:hover { background: #c44d00; }
    .error { color: #e24b4a; font-size: 13px; margin-bottom: 12px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>⚡ SenWatt</h1>
    <p>Gestion intelligente de la consommation électrique</p>
    <?php if ($error): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Email" required value="admin@senwatt.com">
      <input type="password" name="password" placeholder="Mot de passe" required>
      <button type="submit">Se connecter</button>
    </form>
  </div>
</body>
</html>