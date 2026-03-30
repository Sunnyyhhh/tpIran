<?php


require_once __DIR__ . '/../config/database.php';

$slug = $_GET['slug'] ?? null;

if (!$slug) {
    http_response_code(404);
    $pageTitle = "Categorie non trouvee";
    require_once __DIR__ . '/../includes/header.php';
    echo "<h1>Categorie non trouvee</h1>";
    echo "<p>La categorie demandee n'existe pas. <a href='/'>Retour a l'accueil</a></p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = :slug");
$stmt->execute(['slug' => $slug]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(404);
    $pageTitle = "Categorie non trouvee";
    require_once __DIR__ . '/../includes/header.php';
    echo "<h1>Categorie non trouvee</h1>";
    echo "<p>La categorie demandee n'existe pas. <a href='/'>Retour a l'accueil</a></p>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.slug, a.excerpt, a.image, a.image_alt, a.published_at
    FROM articles a
    WHERE a.id_category = :id_category AND a.status = 'published'
    ORDER BY a.published_at DESC
");
$stmt->execute(['id_category' => $category['id']]);
$articles = $stmt->fetchAll();

$pageTitle = $category['name'];
$pageDescription = "Articles de la categorie " . $category['name'] . " sur le conflit en Iran 2026.";

require_once __DIR__ . '/../includes/header.php';
?>

<h1><?= htmlspecialchars($category['name']) ?></h1>
<p class="intro">Articles dans cette categorie.</p>

<section class="articles-list">
    <?php if (empty($articles)): ?>
        <p>Aucun article publie dans cette categorie pour le moment.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <?php if ($article['image']): ?>
                    <img src="<?= htmlspecialchars($article['image']) ?>"
                         alt="<?= htmlspecialchars($article['image_alt'] ?? $article['title']) ?>"
                         class="article-image">
                <?php endif; ?>

                <div class="article-content">
                    <a href="/categorie/<?= htmlspecialchars($slug) ?>" class="article-category">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>

                    <h2>
                        <a href="/article/<?= htmlspecialchars($article['slug']) ?>">
                            <?= htmlspecialchars($article['title']) ?>
                        </a>
                    </h2>

                    <?php if ($article['excerpt']): ?>
                        <p class="article-excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                    <?php endif; ?>

                    <time datetime="<?= $article['published_at'] ?>" class="article-date">
                        <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                    </time>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<div class="back-wrap">
    <a href="/" class="back-link">&larr; Retour a l'accueil</a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
