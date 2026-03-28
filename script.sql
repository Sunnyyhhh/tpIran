CREATE TABLE IF NOT EXISTS wp_users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(60) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,           -- hashé (jamais en clair)
    email           VARCHAR(100) NOT NULL UNIQUE,
    role            VARCHAR(20) DEFAULT 'author',    -- admin, editor, author
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS wp_categories (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    category_name   VARCHAR(100) NOT NULL UNIQUE,
    slug            VARCHAR(120) NOT NULL UNIQUE,    -- pour URL propre (ex: conflit-militaire)
    description     TEXT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS wp_posts (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    id_category         INT NULL,
    id_user             INT NOT NULL,                    -- auteur de l'article
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL UNIQUE,    -- URL normalisée (très important pour le prof)
    content             LONGTEXT NOT NULL,                -- contenu avec H2, H3...
    excerpt             TEXT NULL,                       -- résumé pour la page d'accueil
    featured_image      VARCHAR(500) NULL,               -- chemin de l'image mise en avant
    image_alt           VARCHAR(255) NULL,               -- texte alternatif (SEO)
    status              ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at        DATETIME NULL,                    -- date de publication réelle
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_category) REFERENCES wp_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (id_user)     REFERENCES wp_users(id) ON DELETE RESTRICT
);

CREATE INDEX idx_posts_category ON wp_posts(id_category);
CREATE INDEX idx_posts_status ON wp_posts(status);
CREATE INDEX idx_posts_published ON wp_posts(published_at);
CREATE INDEX idx_posts_slug ON wp_posts(slug);

-- =============================================
-- SCRIPT DE DONNÉES DE TEST - Guerre en Iran 2026
-- À exécuter dans phpMyAdmin
-- =============================================

USE wordpress;

-- 1. Insertion des catégories
INSERT INTO wp_terms (name, slug, term_group) VALUES
('Conflit militaire', 'conflit-militaire', 0),
('Impacts humanitaires', 'impacts-humanitaires', 0),
('Chronologie', 'chronologie', 0),
('Réactions internationales', 'reactions-internationales', 0),
('Analyse géopolitique', 'analyse-geopolitique', 0);

-- Récupération des term_taxonomy_id pour les catégories
INSERT INTO wp_term_taxonomy (term_id, taxonomy, description, parent, count)
SELECT term_id, 'category', '', 0, 0 FROM wp_terms 
WHERE slug IN ('conflit-militaire', 'impacts-humanitaires', 'chronologie', 'reactions-internationales', 'analyse-geopolitique');

-- 2. Insertion des 8 articles (posts)
INSERT INTO wp_posts 
(post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, post_name, post_modified, post_modified_gmt, post_type, comment_count)
VALUES

(1, NOW(), NOW(), 
'<h2>Contexte de l\'attaque</h2><p>Une puissante explosion a secoué le centre-ville de Téhéran dans la nuit du 23 mars 2026.</p><h3>Conséquences immédiates</h3><p>Les secours sont toujours à l\'œuvre sur place.</p>', 
'Explosion massive à Téhéran : bilan provisoire de 87 morts', 
'Une puissante explosion a secoué le centre de Téhéran.', 
'publish', 'explosion-teheran-87-morts', NOW(), NOW(), 'post', 0),

(1, NOW(), NOW(), 
'<h2>Les événements clés</h2><p>Retour détaillé sur la succession rapide d’événements qui ont mené à l’escalade actuelle.</p><h3>15 mars : Déclenchement</h3><p>...</p>', 
'Chronologie : Les 10 jours qui ont fait basculer le Moyen-Orient', 
'Résumé des 10 jours critiques du conflit.', 
'publish', 'chronologie-10-jours-moyen-orient', NOW(), NOW(), 'post', 0),

(1, NOW(), NOW(), 
'<h2>Crise humanitaire en cours</h2><p>Selon l’ONU, plus de 2 millions de personnes ont été déplacées internes en Iran.</p>', 
'Plus de 2 millions de déplacés internes en Iran selon l’ONU', 
'La crise humanitaire s’aggrave rapidement.', 
'publish', 'deux-millions-deplaces-iran-onu', NOW(), NOW(), 'post', 0),

(1, NOW(), NOW(), 
'<h2>Nouvelles sanctions</h2><p>Washington a annoncé un nouveau paquet de sanctions sévères visant le secteur pétrolier et bancaire iranien.</p>', 
'Les États-Unis imposent de nouvelles sanctions économiques sévères', 
'Washington durcit sa position.', 
'publish', 'etats-unis-nouvelles-sanctions-iran', NOW(), NOW(), 'post', 0),

(1, NOW(), NOW(), 
'<h2>Attaque de drones</h2><p>Des drones non identifiés ont visé une base militaire stratégique près d’Ispahan.</p>', 
'Attaque de drones sur une base militaire près d’Ispahan', 
'Une nouvelle escalade dans le conflit.', 
'publish', 'attaque-drones-base-ispahan', NOW(), NOW(), 'post', 0),

(1, NOW(), NOW(), 
'<h2>Position russo-chinoise</h2><p>Moscou et Pékin appellent à une désescalade immédiate et à la reprise des négociations.</p>', 
'La Russie et la Chine appellent à une désescalade immédiate', 
'Les deux puissances demandent le dialogue.', 
'publish', 'russie-chine-desescalade-iran', NOW(), NOW(), 'post', 0),

(1, NOW(), NOW(), 
'<h2>Effondrement économique</h2><p>Le rial iranien a perdu près de 40% de sa valeur en une seule semaine.</p>', 
'Crise économique : Le rial iranien perd 40% de sa valeur en une semaine', 
'L’économie iranienne est durement touchée.', 
'publish', 'rial-iranien-perte-valeur', NOW(), NOW(), 'post', 0),

(1, NOW(), NOW(), 
'<h2>Analyse des experts</h2><p>Le 15 mars 2026 marque le début officiel du conflit selon la majorité des observateurs internationaux.</p>', 
'15 mars 2026 : Début officiel du conflit selon les experts', 
'Que s’est-il vraiment passé ce jour-là ?', 
'publish', '15-mars-debut-conflit-iran', NOW(), NOW(), 'post', 0);

-- 3. Association des articles aux catégories (à adapter selon l'ordre d'insertion)
-- Note : Les IDs des posts commencent généralement après les pages existantes (souvent à partir de 5 ou 6).
-- Exécute d'abord le script ci-dessus, puis ajuste les IDs des posts ci-dessous selon ce que tu vois dans phpMyAdmin (wp_posts).

-- Exemple d'association (remplace les IDs des posts par les vrais IDs après insertion) :
-- Supposons que les nouveaux posts ont les IDs : 10,11,12,13,14,15,16,17

INSERT INTO wp_term_relationships (object_id, term_taxonomy_id, term_order) 
SELECT p.ID, tt.term_taxonomy_id, 0
FROM wp_posts p
JOIN wp_term_taxonomy tt ON tt.taxonomy = 'category'
WHERE p.post_name IN (
    'explosion-teheran-87-morts', 
    'chronologie-10-jours-moyen-orient',
    'deux-millions-deplaces-iran-onu',
    'etats-unis-nouvelles-sanctions-iran',
    'attaque-drones-base-ispahan',
    'russie-chine-desescalade-iran',
    'rial-iranien-perte-valeur',
    '15-mars-debut-conflit-iran'
)
AND tt.term_id IN (SELECT term_id FROM wp_terms WHERE slug IN ('conflit-militaire', 'chronologie', 'impacts-humanitaires', 'reactions-internationales', 'analyse-geopolitique'));