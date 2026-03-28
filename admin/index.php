<?php
// admin/index.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #0a0a0f;
            --surface:  #13131a;
            --surface2: #1a1a24;
            --border:   #1e1e2e;
            --accent:   #e8472a;
            --accent-g: rgba(232,71,42,0.10);
            --green:    #2acd72;
            --yellow:   #f0b429;
            --text:     #f0ede8;
            --muted:    #6b6878;
            --sidebar:  220px;
        }

        html, body { height: 100%; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ─────────────────────── */
        .sidebar {
            width: var(--sidebar);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            padding: 1.5rem 0;
        }

        .sidebar-logo {
            font-family: 'Syne', sans-serif;
            font-size: 0.85rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent);
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }

        .nav {
            flex: 1;
            padding: 1.2rem 0;
        }

        .nav-label {
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 1.5rem;
            margin: 1rem 0 0.4rem;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.6rem 1.5rem;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 400;
            border-left: 2px solid transparent;
            transition: all 0.15s;
        }

        .nav a:hover, .nav a.active {
            color: var(--text);
            background: var(--accent-g);
            border-left-color: var(--accent);
        }

        .nav a svg { flex-shrink: 0; opacity: 0.7; }
        .nav a:hover svg, .nav a.active svg { opacity: 1; }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.75rem;
        }

        .avatar {
            width: 30px; height: 30px;
            background: var(--accent-g);
            border: 1px solid var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--accent);
            flex-shrink: 0;
        }

        .user-info { flex: 1; overflow: hidden; }
        .user-name { font-size: 0.82rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role { font-size: 0.7rem; color: var(--muted); }

        .logout-btn {
            display: block;
            text-align: center;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.78rem;
            transition: all 0.15s;
        }
        .logout-btn:hover { border-color: var(--accent); color: var(--accent); }

        /* ── Main ────────────────────────── */
        .main {
            margin-left: var(--sidebar);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Topbar ──────────────────────── */
        .topbar {
            position: sticky;
            top: 0;
            background: rgba(10,10,15,0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 50;
        }

        .topbar h1 {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1.1rem;
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
            border: none;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }
        .btn-primary:hover { opacity: 0.85; transform: translateY(-1px); }

        .btn-ghost {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--muted);
        }
        .btn-ghost:hover { border-color: var(--text); color: var(--text); }

        /* ── Content ─────────────────────── */
        .content { padding: 2rem; flex: 1; }

        /* ── Flash message ───────────────── */
        .flash {
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            animation: fadeUp 0.3s ease;
        }
        .flash.success { background: rgba(42,205,114,0.12); border: 1px solid rgba(42,205,114,0.3); color: #2acd72; }
        .flash.error   { background: rgba(232,71,42,0.12);  border: 1px solid rgba(232,71,42,0.3);  color: #ff7a63; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Stats cards ─────────────────── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.2rem 1.4rem;
            transition: border-color 0.2s;
        }

        .stat-card:hover { border-color: var(--accent); }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-card.published .stat-value { color: var(--green); }
        .stat-card.draft     .stat-value { color: var(--yellow); }
        .stat-card.accent    .stat-value { color: var(--accent); }

        /* ── Toolbar ─────────────────────── */
        .toolbar {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-wrap {
            position: relative;
            flex: 1;
            min-width: 200px;
        }

        .search-wrap svg {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.6rem 1rem 0.6rem 2.4rem;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .search-input:focus { border-color: var(--accent); }
        .search-input::placeholder { color: var(--muted); }

        select {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.6rem 1rem;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        select:focus { border-color: var(--accent); }

        /* ── Table ───────────────────────── */
        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .table-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-title {
            font-family: 'Syne', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .count-badge {
            background: var(--accent-g);
            border: 1px solid rgba(232,71,42,0.2);
            color: var(--accent);
            border-radius: 20px;
            padding: 0.15rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            background: var(--surface2);
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }

        tbody tr {
            border-top: 1px solid var(--border);
            transition: background 0.12s;
        }

        tbody tr:hover { background: var(--surface2); }

        td {
            padding: 0.9rem 1rem;
            font-size: 0.88rem;
            vertical-align: middle;
        }

        .td-title { max-width: 280px; }
        .td-title strong {
            display: block;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .td-title span {
            font-size: 0.75rem;
            color: var(--muted);
            display: block;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .badge-published { background: rgba(42,205,114,0.12); color: var(--green); border: 1px solid rgba(42,205,114,0.25); }
        .badge-draft     { background: rgba(240,180,41,0.12);  color: var(--yellow); border: 1px solid rgba(240,180,41,0.25); }

        .badge::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .cat-badge {
            background: var(--accent-g);
            color: #c8a29a;
            border: 1px solid rgba(232,71,42,0.15);
            border-radius: 4px;
            padding: 0.15rem 0.5rem;
            font-size: 0.72rem;
        }

        .actions {
            display: flex;
            gap: 0.4rem;
            white-space: nowrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px; height: 30px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
        }

        .action-btn:hover { color: var(--text); border-color: var(--text); background: var(--surface2); }
        .action-btn.delete:hover { color: var(--accent); border-color: var(--accent); }

        /* ── Empty state ─────────────────── */
        .empty {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--muted);
        }
        .empty svg { margin-bottom: 1rem; opacity: 0.3; }
        .empty p { font-size: 0.9rem; }

        /* ── Responsive basic ────────────── */
        @media (max-width: 900px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .sidebar { display: none; }
            .main { margin-left: 0; }
        }
    </style>
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
        <a href="/admin/index.php" class="active">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Articles
        </a>
        <a href="/admin/edit.php">
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
        <a href="/admin/logout.php" class="logout-btn">Déconnexion</a>
    </div>
</aside>

<!-- ═══ MAIN ═══════════════════════════════════════════════ -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <h1>Tableau de bord</h1>
        <a href="/admin/edit.php" class="btn btn-primary">
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
        <form method="GET" action="/admin/index.php">
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
                    <a href="/admin/index.php" class="btn btn-ghost">Réinitialiser</a>
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
                                <a href="/admin/edit.php?id=<?= $article['id'] ?>"
                                   class="action-btn" title="Modifier">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <!-- Supprimer -->
                                <a href="/admin/delete.php?id=<?= $article['id'] ?>"
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
