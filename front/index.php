<?php
/**
 * Page d'accueil - Liste des articles
 */

require_once __DIR__ . '/../config/database.php';

// Recuperer les articles publies
$stmt = $pdo->prepare("
    SELECT a.id, a.title, a.slug, a.excerpt, a.image, a.image_alt, a.published_at,
           c.name AS category_name, c.slug AS category_slug
    FROM articles a
    LEFT JOIN categories c ON a.id_category = c.id
    WHERE a.status = 'published'
    ORDER BY a.published_at DESC
");
$stmt->execute();
$articles = $stmt->fetchAll();

// SEO
$pageTitle = "Accueil";
$pageDescription = "Suivez l'actualite du conflit en Iran 2026 : analyses, chronologie, reactions internationales.";

require_once __DIR__ . '/../includes/header.php';
?>

<h1>Actualites sur la guerre en Iran</h1>
<p class="intro">Analyses, chronologie et reactions internationales sur le conflit de 2026.</p>

<section class="articles-list">
    <?php if (empty($articles)): ?>
        <p>Aucun article publie pour le moment.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <?php if ($article['image']): ?>
                    <img src="<?= htmlspecialchars($article['image']) ?>"
                         alt="<?= htmlspecialchars($article['image_alt'] ?? $article['title']) ?>"
                         class="article-image">
                <?php endif; ?>

                <div class="article-content">
                    <?php if ($article['category_name']): ?>
                        <a href="/categorie/<?= htmlspecialchars($article['category_slug']) ?>" class="article-category">
                            <?= htmlspecialchars($article['category_name']) ?>
                        </a>
                    <?php endif; ?>

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
