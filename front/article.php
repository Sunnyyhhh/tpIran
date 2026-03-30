<?php


require_once __DIR__ . '/../config/database.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    http_response_code(404);
    $pageTitle = "Article non trouve";
    require_once __DIR__ . '/../includes/header.php';
    echo "<h1>Article non trouve</h1>";
    echo "<p>L'article demande n'existe pas. <a href='/'>Retour a l'accueil</a></p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.username AS author
    FROM articles a
    LEFT JOIN categories c ON a.id_category = c.id
    LEFT JOIN users u ON a.id_user = u.id
    WHERE a.slug = :slug AND a.status = 'published'
");
$stmt->execute(['slug' => $slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = "Article non trouve";
    require_once __DIR__ . '/../includes/header.php';
    echo "<h1>Article non trouve</h1>";
    echo "<p>L'article demande n'existe pas. <a href='/'>Retour a l'accueil</a></p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$pageTitle = $article['title'];
$pageDescription = $article['excerpt'] ?? substr(strip_tags($article['content']), 0, 160);
$pageImage = $article['image'] ?? null;
$pageUrl = "http://" . $_SERVER['HTTP_HOST'] . "/article/" . $article['slug'];

require_once __DIR__ . '/../includes/header.php';
?>

<article class="article-detail">
    <header class="article-header">
        <?php if ($article['category_name']): ?>
            <a href="/categorie/<?= htmlspecialchars($article['category_slug']) ?>" class="article-category">
                <?= htmlspecialchars($article['category_name']) ?>
            </a>
        <?php endif; ?>

        <h1><?= htmlspecialchars($article['title']) ?></h1>

        <div class="article-meta">
            <time datetime="<?= $article['published_at'] ?>">
                Publie le <?= date('d/m/Y a H:i', strtotime($article['published_at'])) ?>
            </time>
            <?php if ($article['author']): ?>
                <span class="author">par <?= htmlspecialchars($article['author']) ?></span>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($article['image']): ?>
        <figure class="article-figure">
            <img src="<?= htmlspecialchars($article['image']) ?>"
                 alt="<?= htmlspecialchars($article['image_alt'] ?? $article['title']) ?>"
                 class="article-image-full">
            <?php if ($article['image_alt']): ?>
                <figcaption><?= htmlspecialchars($article['image_alt']) ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <div class="article-body">
        <?= $article['content'] ?>
    </div>

    <footer class="article-footer">
        <a href="/" class="back-link">&larr; Retour aux actualites</a>
    </footer>
</article>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
