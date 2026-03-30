<?php
// admin/index.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$user = current_user();

// ── Stats globales ──────────────────────────────────────────────
$stats = [];
$stats['total']     = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$stats['published'] = $pdo->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn();
$stats['draft']     = $pdo->query("SELECT COUNT(*) FROM articles WHERE status='draft'")->fetchColumn();
$stats['categories']= $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// ── Filtres ─────────────────────────────────────────────────────
$filter_status   = $_GET['status']   ?? '';
$filter_category = $_GET['category'] ?? '';
$search          = trim($_GET['q']   ?? '');

$where  = [];
$params = [];

if ($filter_status)   { $where[] = 'a.status = ?';      $params[] = $filter_status; }
if ($filter_category) { $where[] = 'a.id_category = ?'; $params[] = $filter_category; }
if ($search)          { $where[] = 'a.title LIKE ?';    $params[] = "%$search%"; }

$sql = "
    SELECT a.*, c.name AS category_name, u.username AS author
    FROM articles a
    LEFT JOIN categories c ON c.id = a.id_category
    LEFT JOIN users u ON u.id = a.id_user
" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . "
    ORDER BY a.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// ── Liste des catégories pour le filtre ─────────────────────────
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// ── Message flash ────────────────────────────────────────────────
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Backoffice Iran War Info</title>
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body>

<!-- ═══ SIDEBAR ═══════════════════════════════════════════ -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="dot"></div>
        Iran War Info
    </div>

    <nav class="nav">
        <div class="nav-label">Contenu</div>
        <a href="/back/index.php" class="active">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Articles
        </a>
        <a href="/back/edit.php">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-5"/><path d="M16 3l5 5-9 9H7v-5z"/></svg>
            Nouvel article
        </a>

        <div class="nav-label">Site</div>
        <a href="/" target="_blank">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg>
            Voir le site
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="avatar"><?= strtoupper(substr($user['username'], 0, 2)) ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($user['username']) ?></div>
                <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
            </div>
        </div>
        <a href="/back/logout.php" class="logout-btn">Déconnexion</a>
    </div>
</aside>

<!-- ═══ MAIN ═══════════════════════════════════════════════ -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <h1>Tableau de bord</h1>
        <a href="/back/edit.php" class="btn btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nouvel article
        </a>
    </div>

    <div class="content">

        <!-- Flash -->
        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total articles</div>
                <div class="stat-value"><?= $stats['total'] ?></div>
            </div>
            <div class="stat-card published">
                <div class="stat-label">Publiés</div>
                <div class="stat-value"><?= $stats['published'] ?></div>
            </div>
            <div class="stat-card draft">
                <div class="stat-label">Brouillons</div>
                <div class="stat-value"><?= $stats['draft'] ?></div>
            </div>
            <div class="stat-card accent">
                <div class="stat-label">Catégories</div>
                <div class="stat-value"><?= $stats['categories'] ?></div>
            </div>
        </div>

        <!-- Toolbar / Filtres -->
        <form method="GET" action="/back/index.php">
            <div class="toolbar">
                <div class="search-wrap">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input class="search-input" type="text" name="q"
                           placeholder="Rechercher un article…"
                           value="<?= htmlspecialchars($search) ?>">
                </div>

                <select name="status" onchange="this.form.submit()">
                    <option value="">Tous statuts</option>
                    <option value="published" <?= $filter_status === 'published' ? 'selected' : '' ?>>Publiés</option>
                    <option value="draft"     <?= $filter_status === 'draft'     ? 'selected' : '' ?>>Brouillons</option>
                </select>

                <select name="category" onchange="this.form.submit()">
                    <option value="">Toutes catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= $filter_category == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($search || $filter_status || $filter_category): ?>
                    <a href="/back/index.php" class="btn btn-ghost">Réinitialiser</a>
                <?php endif; ?>

                <button type="submit" class="btn btn-ghost">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Filtrer
                </button>
            </div>
        </form>

        <!-- Table articles -->
        <div class="table-wrap">
            <div class="table-header">
                <span class="table-title">Articles</span>
                <span class="count-badge"><?= count($articles) ?> résultat<?= count($articles) > 1 ? 's' : '' ?></span>
            </div>

            <?php if (empty($articles)): ?>
                <div class="empty">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <p>Aucun article trouvé.</p>
                </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Auteur</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td style="color:var(--muted);font-size:.78rem"><?= $article['id'] ?></td>

                        <td class="td-title">
                            <strong><?= htmlspecialchars($article['title']) ?></strong>
                            <span>/<?= htmlspecialchars($article['slug']) ?></span>
                        </td>

                        <td>
                            <?php if ($article['category_name']): ?>
                                <span class="cat-badge"><?= htmlspecialchars($article['category_name']) ?></span>
                            <?php else: ?>
                                <span style="color:var(--muted);font-size:.8rem">—</span>
                            <?php endif; ?>
                        </td>

                        <td style="color:var(--muted);font-size:.83rem">
                            <?= htmlspecialchars($article['author']) ?>
                        </td>

                        <td>
                            <span class="badge badge-<?= $article['status'] ?>">
                                <?= $article['status'] === 'published' ? 'Publié' : 'Brouillon' ?>
                            </span>
                        </td>

                        <td style="color:var(--muted);font-size:.78rem;white-space:nowrap">
                            <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                        </td>

                        <td>
                            <div class="actions">
                                <!-- Voir FO -->
                                <a href="/article/<?= htmlspecialchars($article['slug']) ?>"
                                   target="_blank" class="action-btn" title="Voir sur le site">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <!-- Modifier -->
                                <a href="/back/edit.php?id=<?= $article['id'] ?>"
                                   class="action-btn" title="Modifier">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <!-- Supprimer -->
                                <a href="/back/delete.php?id=<?= $article['id'] ?>"
                                   class="action-btn delete" title="Supprimer"
                                   onclick="return confirm('Supprimer cet article ?')">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

</body>
</html>
