<?php
// admin/edit.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

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
        header('Location: /back/index.php');
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
    <link rel="stylesheet" href="/public/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo"><span class="dot"></span> Iran War Info</div>
    <nav class="nav">
        <div class="nav-label">Contenu</div>
        <a href="/back/index.php">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Articles
        </a>
        <a href="/back/edit.php" class="active">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Nouvel article
        </a>
        <div class="nav-label">Site</div>
        <a href="/">
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
        <a href="/back/logout.php" class="logout-btn">Deconnexion</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1><?= $article ? 'Modifier l\'article' : 'Nouvel article' ?></h1>
        <div class="topbar-actions">
            <a href="/back/index.php" class="btn btn-ghost">← Retour</a>
            <button type="submit" form="article-form" name="status_action" value="draft" class="btn btn-ghost">Enregistrer brouillon</button>
            <button type="submit" form="article-form" name="status_action" value="published" class="btn btn-success">Publier →</button>
        </div>
    </div>

    <div class="content">

        <?php if ($flash): ?>
            <div class="flash <?= htmlspecialchars($flash['type']) ?>"><?= htmlspecialchars($flash['message']) ?></div>
        <?php endif; ?>

        <form id="article-form" method="POST" action="/back/save.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $article['id'] ?? '' ?>">
            <input type="hidden" name="existing_image" value="<?= htmlspecialchars($article['image'] ?? '') ?>">
            <!-- Le statut est déterminé par le bouton cliqué -->
            <input type="hidden" name="status" id="status-field" value="<?= htmlspecialchars($article['status'] ?? 'draft') ?>">

            <div class="grid">

                <!-- Colonne gauche : contenu principal -->
                <div>
                    <div class="card card-content">
                        <div class="card-header">Contenu</div>
                        <div class="card-body">

                            <div class="field">
                                <label for="title">Titre <span class="required">*</span></label>
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
                                <label>Contenu <span class="required">*</span></label>
                                <div class="tinymce-wrap">
                                    <textarea id="content" name="content"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Colonne droite : paramètres -->
                <div class="sidebar-cards">

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
                        <div class="card-body card-info">
                            <div>Créé le : <span class="info-value"><?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></span></div>
                            <div>Modifié le : <span class="info-value"><?= date('d/m/Y H:i', strtotime($article['updated_at'])) ?></span></div>
                            <div>Slug : <span class="info-slug">/<?= htmlspecialchars($article['slug']) ?></span></div>
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
    content_style: 'body { font-family: system-ui; font-size: 14px; }',
    branding: false,
    promotion: false,
    setup: function(editor) {
        editor.on('change', function() { editor.save(); });
    }
});
</script>
</body>
</html>
