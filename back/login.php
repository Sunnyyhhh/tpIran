<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) { header('Location: /back/index.php'); exit; }
require_once __DIR__ . '/../config/database.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: /back/index.php'); exit;
        } else { $error = 'Identifiants incorrects.'; }
    } else { $error = 'Veuillez remplir tous les champs.'; }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Backoffice</title>
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body class="login-body">
<div class="login-wrap">
    <div class="login-logo"><span class="dot"></span> Iran War Info</div>
    <h1>Backoffice</h1>
    <p class="login-sub">Connectez-vous pour gerer les contenus.</p>
    <div class="login-card">
        <?php if ($error): ?><div class="login-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <label>Identifiant</label>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            <label>Mot de passe</label>
            <input type="password" name="password" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
    <p class="login-hint">Compte par defaut : <strong>admin</strong> / <strong>password</strong></p>
</div>
</body>
</html>
