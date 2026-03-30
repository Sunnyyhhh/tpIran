<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /back/index.php');
    exit;
}

$id         = intval($_POST['id'] ?? 0);
$title      = trim($_POST['title']      ?? '');
$content    = $_POST['content']         ?? '';  
$excerpt    = trim($_POST['excerpt']    ?? '');
$image_alt  = trim($_POST['image_alt']  ?? '');
$status     = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
$id_category= intval($_POST['id_category'] ?? 0) ?: null;
$user       = current_user();

if (!$title || !$content) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Le titre et le contenu sont obligatoires.'];
    $redirect = $id ? "/back/edit.php?id=$id" : "/back/edit.php";
    header("Location: $redirect");
    exit;
}

function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, ['à'=>'a','â'=>'a','ä'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
                           'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u',
                           'ç'=>'c','ñ'=>'n','æ'=>'ae','œ'=>'oe']);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return trim($text, '-');
}

$slug_base = slugify($title);

if ($id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ? AND id != ?");
    $stmt->execute([$slug_base, $id]);
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = ?");
    $stmt->execute([$slug_base]);
}
$slug = $slug_base;
if ($stmt->fetchColumn() > 0) {
    $slug = $slug_base . '-' . time();
}

$image_path = $_POST['existing_image'] ?? null; 

if (!empty($_FILES['image']['name'])) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Format image non autorisé (jpg, png, webp, gif).'];
        header("Location: /back/edit.php" . ($id ? "?id=$id" : ""));
        exit;
    }

    $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = $slug . '-' . time() . '.' . strtolower($ext);
    $dest     = __DIR__ . '/../public/uploads/' . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        $image_path = '/public/uploads/' . $filename;
    }
}

$published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;

if ($id) {
    $stmt = $pdo->prepare("
        UPDATE articles SET
            title        = ?,
            slug         = ?,
            content      = ?,
            excerpt      = ?,
            image        = ?,
            image_alt    = ?,
            status       = ?,
            id_category  = ?,
            published_at = ?,
            updated_at   = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$title, $slug, $content, $excerpt, $image_path, $image_alt, $status, $id_category, $published_at, $id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Article modifié avec succès.'];
} else {
    $stmt = $pdo->prepare("
        INSERT INTO articles (id_category, id_user, title, slug, content, excerpt, image, image_alt, status, published_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$id_category, $user['id'], $title, $slug, $content, $excerpt, $image_path, $image_alt, $status, $published_at]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Article créé avec succès.'];
}

header('Location: /back/index.php');
exit;
