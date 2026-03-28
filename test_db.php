<?php
/**
 * Test de connexion BDD - A SUPPRIMER apres test
 */

require_once __DIR__ . '/config/db.php';

echo "<h1>Test connexion BDD</h1>";

// Test 1 : Connexion
echo "<p>Connexion PDO : OK</p>";

// Test 2 : Lire les categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

echo "<h2>Categories (" . count($categories) . ")</h2>";
echo "<ul>";
foreach ($categories as $cat) {
    echo "<li>{$cat['name']} (slug: {$cat['slug']})</li>";
}
echo "</ul>";

// Test 3 : Lire les articles
$stmt = $pdo->query("SELECT id, title, slug, status FROM articles LIMIT 5");
$articles = $stmt->fetchAll();

echo "<h2>Articles (5 premiers)</h2>";
echo "<ul>";
foreach ($articles as $art) {
    echo "<li>[{$art['status']}] {$art['title']}</li>";
}
echo "</ul>";

echo "<p style='color:green; font-weight:bold;'>Tout fonctionne !</p>";
