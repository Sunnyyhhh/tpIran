-- =============================================
-- BASE DE DONNÉES : Guerre en Iran 2026
-- =============================================

CREATE DATABASE IF NOT EXISTS iran_war_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE iran_war_db;

-- ─────────────────────────────────────────
-- TABLE : Utilisateurs (backoffice)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(60)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin', 'editor') DEFAULT 'editor',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- TABLE : Catégories
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL UNIQUE,
    slug    VARCHAR(120) NOT NULL UNIQUE
);

-- ─────────────────────────────────────────
-- TABLE : Articles
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS articles (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    id_category     INT NULL,
    id_user         INT NOT NULL,
    title           VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL UNIQUE,
    content         LONGTEXT NOT NULL,
    excerpt         TEXT NULL,
    image           VARCHAR(500) NULL,
    image_alt       VARCHAR(255) NULL,
    status          ENUM('draft', 'published') DEFAULT 'draft',
    published_at    DATETIME NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_category) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (id_user)     REFERENCES users(id) ON DELETE RESTRICT
);

-- Index pour les performances et le SEO
CREATE INDEX idx_slug      ON articles(slug);
CREATE INDEX idx_status    ON articles(status);
CREATE INDEX idx_category  ON articles(id_category);

-- =============================================
-- DONNÉES DE TEST
-- =============================================

-- Admin par défaut (mdp : admin1234)
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('redacteur', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor');
-- ⚠️  Les deux comptes ont le mot de passe : password
-- Pour admin1234, remplace le hash via : password_hash('admin1234', PASSWORD_BCRYPT)

-- Catégories
INSERT INTO categories (name, slug) VALUES
('Conflit militaire',        'conflit-militaire'),
('Humanitaire',              'humanitaire'),
('Chronologie',              'chronologie'),
('Réactions internationales','reactions-internationales'),
('Économie',                 'economie');

-- Articles
INSERT INTO articles (id_category, id_user, title, slug, content, excerpt, image_alt, status, published_at) VALUES

(1, 1,
'Explosion à Téhéran : 87 morts',
'explosion-teheran-87-morts',
'<h2>Contexte</h2><p>Une explosion a secoué le centre de Téhéran le 23 mars 2026.</p><h3>Bilan</h3><p>87 morts et plus de 200 blessés selon les autorités iraniennes.</p>',
'Une explosion meurtrière frappe le centre de Téhéran.',
'Vue aérienne des destructions à Téhéran',
'published', NOW()),

(3, 1,
'Les 10 jours qui ont tout changé',
'chronologie-10-jours',
'<h2>15 mars : Déclenchement</h2><p>Les premières frappes ont eu lieu dans la nuit du 15 mars.</p><h3>20 mars : Escalade</h3><p>L\'escalade s\'est accélérée avec l\'entrée de nouveaux acteurs.</p>',
'Retour sur les 10 jours qui ont fait basculer le conflit.',
'Carte du Moyen-Orient avec les zones de conflit',
'published', NOW()),

(2, 1,
'2 millions de déplacés en Iran',
'deux-millions-deplaces-iran',
'<h2>Crise humanitaire</h2><p>Selon l\'ONU, plus de 2 millions de personnes ont fui les zones de combat.</p><h3>Aide internationale</h3><p>Plusieurs ONG sont mobilisées sur le terrain.</p>',
'La crise humanitaire s\'aggrave, 2 millions de déplacés.',
'Camp de réfugiés iraniens',
'published', NOW()),

(4, 1,
'Les États-Unis imposent de nouvelles sanctions',
'sanctions-usa-iran-2026',
'<h2>Nouvelles sanctions</h2><p>Washington a annoncé un paquet de sanctions visant le pétrole et les banques iraniennes.</p><h3>Réaction de Téhéran</h3><p>L\'Iran a qualifié ces mesures d\'acte de guerre économique.</p>',
'Washington durcit sa position avec de nouvelles sanctions.',
'Siège du département d\'État américain',
'published', NOW()),

(1, 1,
'Drones sur la base d\'Ispahan',
'attaque-drones-ispahan',
'<h2>Attaque nocturne</h2><p>Des drones non identifiés ont visé une base militaire près d\'Ispahan.</p><h3>Revendication</h3><p>Aucun groupe n\'a revendiqué l\'attaque à ce stade.</p>',
'Une base militaire iranienne visée par des drones.',
'Photo satellite de la base militaire d\'Ispahan',
'published', NOW()),

(4, 1,
'Russie et Chine appellent à la paix',
'russie-chine-desescalade',
'<h2>Position commune</h2><p>Moscou et Pékin ont publié une déclaration commune appelant à la désescalade.</p><h3>Proposition de médiation</h3><p>Les deux pays proposent d\'accueillir des négociations.</p>',
'Moscou et Pékin demandent une désescalade immédiate.',
'Drapeaux russe et chinois à l\'ONU',
'published', NOW()),

(5, 1,
'Le rial perd 40% en une semaine',
'rial-iranien-chute-40-pourcent',
'<h2>Effondrement monétaire</h2><p>Le rial iranien a perdu 40% de sa valeur en seulement 7 jours.</p><h3>Conséquences</h3><p>Les prix des denrées de base ont doublé dans plusieurs villes.</p>',
'L\'économie iranienne s\'effondre, le rial en chute libre.',
'Graphique de la chute du rial iranien',
'published', NOW()),

(3, 1,
'15 mars 2026 : date officielle du début du conflit',
'15-mars-2026-debut-conflit',
'<h2>Analyse</h2><p>Les experts s\'accordent sur le 15 mars comme date de déclenchement officiel.</p><h3>Causes profondes</h3><p>Les tensions couvaient depuis plusieurs années.</p>',
'Le 15 mars 2026 retenu comme date officielle du début du conflit.',
'Archives historiques sur le conflit iranien',
'published', NOW());