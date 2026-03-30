<?php
// admin/edit.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

$user       = current_user();
$id         = intval($_GET['id'] ?? 0);
$article    = null;
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Mode édition : charger l'article existant
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if (!$article) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Article introuvable.'];
        header('Location: /admin/index.php');
        exit;
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $article ? 'Modifier' : 'Nouvel article' ?> — Backoffice</title>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #0f0f14; --surface: #13131a; --border: #1e1e2e;
            --accent: #e8472a; --text: #f0ede8; --muted: #6b6878;
            --green: #2acd72; --sidebar: 200px;
        }
        body { background: var(--bg); color: var(--text); font-family: system-ui, sans-serif; display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: var(--sidebar); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; top:0; left:0; bottom:0; padding: 1.2rem 0; }
        .s-logo { font-size: .82rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--accent); padding: 0 1.2rem 1.2rem; border-bottom: 1px solid var(--border); }
        .nav a { display: flex; align-items: center; gap: .6rem; padding: .55rem 1.2rem; color: var(--muted); text-decoration: none; font-size: .85rem; border-left: 2px solid transparent; transition: all .15s; }
        .nav a:hover, .nav a.active { color: var(--text); background: rgba(232,71,42,.08); border-left-color: var(--accent); }
        .nav a svg { width: 14px; height: 14px; flex-shrink: 0; }
        .s-label { font-size: .68rem; color: var(--muted); padding: .8rem 1.2rem .3rem; letter-spacing: .1em; text-transform: uppercase; }
        .s-foot { margin-top: auto; padding: 1rem 1.2rem; border-top: 1px solid var(--border); }
        .avatar { width: 28px; height: 28px; border-radius: 50%; background: rgba(232,71,42,.12); display: flex; align-items: center; justify-content: center; font-size: .7rem; font-weight: 700; color: var(--accent); }
        .u-row { display: flex; align-items: center; gap: .6rem; margin-bottom: .6rem; }
        .u-name { font-size: .8rem; font-weight: 500; }
        .u-role { font-size: .7rem; color: var(--muted); }
        .lo { display: block; text-align: center; padding: .45rem; border: 1px solid var(--border); border-radius: 6px; color: var(--muted); text-decoration: none; font-size: .75rem; }

        /* Main */
        .main { margin-left: var(--sidebar); flex: 1; display: flex; flex-direction: column; }
        .topbar { position: sticky; top: 0; background: rgba(15,15,20,.9); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); padding: .85rem 1.5rem; display: flex; align-items: center; justify-content: space-between; z-index: 50; }
        .topbar h1 { font-size: 1rem; font-weight: 700; }
        .topbar-actions { display: flex; gap: .5rem; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: .4rem; padding: .5rem 1rem; border-radius: 7px; font-size: .83rem; font-weight: 500; cursor: pointer; text-decoration: none; border: none; transition: all .15s; }
        .btn-primary { background: var(--accent); color: #fff; }
        .btn-primary:hover { opacity: .85; }
        .btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--muted); }
        .btn-ghost:hover { border-color: var(--text); color: var(--text); }
        .btn-success { background: var(--green); color: #fff; }
        .btn-success:hover { opacity: .85; }

        /* Content */
        .content { padding: 1.5rem; flex: 1; }
        .grid { display: grid; grid-template-columns: 1fr 300px; gap: 1.2rem; align-items: start; }

        /* Flash */
        .flash { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.2rem; font-size: .85rem; }
        .flash.success { background: rgba(42,205,114,.1); border: 1px solid rgba(42,205,114,.3); color: var(--green); }
        .flash.error   { background: rgba(232,71,42,.1);  border: 1px solid rgba(232,71,42,.3);  color: #ff7a63; }

        /* Cards */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .card-header { padding: .85rem 1.2rem; border-bottom: 1px solid var(--border); font-size: .82rem; font-weight: 600; color: var(--muted); letter-spacing: .05em; text-transform: uppercase; }
        .card-body { padding: 1.2rem; }

        /* Form fields */
        .field { margin-bottom: 1.1rem; }
        .field:last-child { margin-bottom: 0; }
        label { display: block; font-size: .75rem; font-weight: 500; letter-spacing: .07em; text-transform: uppercase; color: var(--muted); margin-bottom: .4rem; }
        input[type="text"], input[type="file"], select, textarea {
            width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: 8px;
            padding: .7rem .9rem; color: var(--text); font-size: .88rem; font-family: inherit;
            outline: none; transition: border-color .2s;
        }
        input:focus, select:focus, textarea:focus { border-color: var(--accent); }
        textarea { resize: vertical; min-height: 80px; }
        select option { background: var(--surface); }

        /* TinyMCE wrapper */
        .tinymce-wrap { border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        .tinymce-wrap:focus-within { border-color: var(--accent); }

        /* Status radio */
        .radio-group { display: flex; gap: .8rem; }
        .radio-option { display: flex; align-items: center; gap: .4rem; cursor: pointer; }
        .radio-option input { width: auto; }
        .radio-option span { font-size: .88rem; color: var(--text); }

        /* Image preview */
        .img-preview { margin-top: .6rem; }
        .img-preview img { width: 100%; border-radius: 6px; border: 1px solid var(--border); object-fit: cover; max-height: 160px; }

        /* Divider */
        hr { border: none; border-top: 1px solid var(--border); margin: 1rem 0; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="s-logo">⬤ Iran War Info</div>
    <nav class="nav">
        <div class="s-label">Contenu</div>
        <a href="/admin/index.php">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Articles
        </a>
        <a href="/admin/edit.php" class="active">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Nouvel article
        </a>
        <div class="s-label">Site</div>
        <a href="/" target="_blank">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg>
            Voir le site
        </a>
    </nav>
    <div class="s-foot">
        <div class="u-row">
            <div class="avatar"><?= strtoupper(substr($user['username'], 0, 2)) ?></div>
            <div><div class="u-name"><?= htmlspecialchars($user['username']) ?></div><div class="u-role"><?= htmlspecialchars($user['role']) ?></div></div>
        </div>
        <a href="/admin/logout.php" class="lo">Déconnexion</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1><?= $article ? 'Modifier l\'article' : 'Nouvel article' ?></h1>
        <div class="topbar-actions">
            <a href="/admin/index.php" class="btn btn-ghost">← Retour</a>
            <button type="submit" form="article-form" name="status_action" value="draft" class="btn btn-ghost">Enregistrer brouillon</button>
            <button type="submit" form="article-form" name="status_action" value="published" class="btn btn-success">Publier →</button>
        </div>
    </div>

    <div class="content">

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <form id="article-form" method="POST" action="/admin/save.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $article['id'] ?? '' ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($article['image'] ?? '') ?>">
            <!-- Le statut est déterminé par le bouton cliqué -->
            <input type="hidden" name="status" id="status-field" value="<?= htmlspecialchars($article['status'] ?? 'draft') ?>">

            <div class="grid">

                <!-- Colonne gauche : contenu principal -->
                <div>
                    <div class="card" style="margin-bottom:1.2rem">
                        <div class="card-header">Contenu</div>
                        <div class="card-body">

                            <div class="field">
                                <label for="title">Titre <span style="color:var(--accent)">*</span></label>
                                <input type="text" id="title" name="title" required
                                       placeholder="Titre de l'article"
                                       value="<?= htmlspecialchars($article['title'] ?? '') ?>">
                            </div>

                            <div class="field">
                                <label for="excerpt">Résumé (extrait affiché en page d'accueil)</label>
                                <textarea id="excerpt" name="excerpt" rows="3"
                                          placeholder="Courte description de l'article…"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
                            </div>

                            <div class="field">
                                <label>Contenu <span style="color:var(--accent)">*</span></label>
                                <div class="tinymce-wrap">
                                    <textarea id="content" name="content"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Colonne droite : paramètres -->
                <div style="display:flex;flex-direction:column;gap:1.2rem">

                    <!-- Catégorie -->
                    <div class="card">
                        <div class="card-header">Catégorie</div>
                        <div class="card-body">
                            <div class="field">
                                <label for="id_category">Catégorie</label>
                                <select id="id_category" name="id_category">
                                    <option value="">— Aucune —</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"
                                            <?= ($article['id_category'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Image -->
                    <div class="card">
                        <div class="card-header">Image à la une</div>
                        <div class="card-body">
                            <?php if (!empty($article['image'])): ?>
                                <div class="img-preview">
                                    <img src="<?= htmlspecialchars($article['image']) ?>"
                                         alt="<?= htmlspecialchars($article['image_alt'] ?? '') ?>">
                                </div>
                                <hr>
                            <?php endif; ?>
                            <div class="field">
                                <label for="image">Choisir une image</label>
                                <input type="file" id="image" name="image" accept="image/*">
                            </div>
                            <div class="field">
                                <label for="image_alt">Texte alternatif (alt)</label>
                                <input type="text" id="image_alt" name="image_alt"
                                       placeholder="Description de l'image pour le SEO"
                                       value="<?= htmlspecialchars($article['image_alt'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Infos -->
                    <?php if ($article): ?>
                    <div class="card">
                        <div class="card-header">Informations</div>
                        <div class="card-body" style="font-size:.8rem;color:var(--muted);line-height:1.8">
                            <div>Créé le : <span style="color:var(--text)"><?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></span></div>
                            <div>Modifié le : <span style="color:var(--text)"><?= date('d/m/Y H:i', strtotime($article['updated_at'])) ?></span></div>
                            <div>Slug : <span style="color:var(--accent)">/<?= htmlspecialchars($article['slug']) ?></span></div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Met à jour le champ status selon le bouton cliqué
document.querySelectorAll('[name="status_action"]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('status-field').value = this.value;
    });
});

// Init TinyMCE
tinymce.init({
    selector: '#content',
    language: 'fr_FR',
    height: 420,
    menubar: false,
    plugins: 'lists link image code table',
    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
    content_style: 'body { font-family: system-ui; font-size: 14px; background: #0f0f14; color: #f0ede8; }',
    skin: 'oxide-dark',
    content_css: 'dark',
    branding: false,
    promotion: false,
    setup: function(editor) {
        editor.on('change', function() { editor.save(); });
    }
});
</script>
</body>
</html>
