<?php
/**
 * Connexion PDO à la base de données
 * Utilise les variables d'environnement Docker
 */

$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'iran_war_db';
$user = getenv('DB_USER') ?: 'iran_user';
$pass = getenv('DB_PASS') ?: 'iran_pass_2026';

$dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log("Erreur connexion BDD : " . $e->getMessage());
    die("Erreur de connexion à la base de données.");
}
