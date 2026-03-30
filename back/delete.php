<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Article introuvable.'];
    header('Location: /back/index.php');
    exit;
}

// Vérifier que l'article existe
$stmt = $pdo->prepare("SELECT id, image FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Article introuvable.'];
    header('Location: /back/index.php');
    exit;
}

// Supprimer l'image associée si elle existe
if ($article['image'] && file_exists(__DIR__ . '/../public/uploads/' . basename($article['image']))) {
    unlink(__DIR__ . '/../public/uploads/' . basename($article['image']));
}

// Supprimer l'article
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['flash'] = ['type' => 'success', 'message' => 'Article supprimé avec succès.'];
header('Location: /back/index.php');
exit;
