<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) { header('Location: /admin/index.php'); exit; }
require_once __DIR__ . '/../config/db.php';
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
            header('Location: /admin/index.php'); exit;
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
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{background:#0f0f14;color:#f0ede8;font-family:system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .wrap{width:100%;max-width:400px;padding:2rem}
        .logo{font-size:.9rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#e8472a;margin-bottom:2rem}
        h1{font-size:1.8rem;font-weight:800;margin-bottom:.4rem}
        .sub{color:#6b6878;font-size:.9rem;margin-bottom:2rem}
        .card{background:#13131a;border:1px solid #1e1e2e;border-radius:12px;padding:1.8rem}
        label{display:block;font-size:.75rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:#6b6878;margin-bottom:.4rem}
        input{width:100%;background:#0f0f14;border:1px solid #1e1e2e;border-radius:8px;padding:.8rem 1rem;color:#f0ede8;font-size:.95rem;outline:none;margin-bottom:1rem}
        input:focus{border-color:#e8472a}
        .error{background:rgba(232,71,42,.12);border:1px solid rgba(232,71,42,.3);border-radius:8px;padding:.7rem 1rem;font-size:.85rem;color:#ff7a63;margin-bottom:1rem}
        button{width:100%;background:#e8472a;color:#fff;border:none;border-radius:8px;padding:.85rem;font-size:.95rem;font-weight:700;cursor:pointer;margin-top:.3rem}
        button:hover{opacity:.88}
        .hint{text-align:center;color:#6b6878;font-size:.75rem;margin-top:1.2rem}
    </style>
</head>
<body>
<div class="wrap">
    <div class="logo">⬤ Iran War Info</div>
    <h1>Backoffice</h1>
    <p class="sub">Connectez-vous pour gérer les contenus.</p>
    <div class="card">
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <label>Identifiant</label>
            <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            <label>Mot de passe</label>
            <input type="password" name="password" required>
            <button type="submit">Se connecter →</button>
        </form>
    </div>
    <p class="hint">Compte par défaut : <strong>admin</strong> / <strong>password</strong></p>
</div>
</body>
</html>
