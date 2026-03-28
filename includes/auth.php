<?php
// includes/auth.php
// À inclure en haut de chaque page du /admin

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    header('Location: /admin/login.php');
    exit;
}

// Helper : utilisateur connecté
function current_user(): array {
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role'     => $_SESSION['role'],
    ];
}

function is_admin(): bool {
    return ($_SESSION['role'] ?? '') === 'admin';
}
