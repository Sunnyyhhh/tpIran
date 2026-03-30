<?php
/**
 * Header FrontOffice - SEO optimise
 *
 * Variables attendues (optionnelles) :
 * - $pageTitle : titre de la page
 * - $pageDescription : meta description
 * - $pageImage : image Open Graph
 * - $pageUrl : URL canonique
 */

$siteName = "Guerre en Iran 2026";
$defaultDescription = "Suivez l'actualite du conflit en Iran : analyses, chronologie, reactions internationales et situation humanitaire.";

$title = isset($pageTitle) ? htmlspecialchars($pageTitle) . " | " . $siteName : $siteName;
$description = isset($pageDescription) ? htmlspecialchars($pageDescription) : $defaultDescription;
$image = isset($pageImage) ? htmlspecialchars($pageImage) : "/public/images/og-default.jpg";
$url = isset($pageUrl) ? htmlspecialchars($pageUrl) : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO -->
    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">
    <link rel="canonical" href="<?= $url ?>">

    <!-- Open Graph (Facebook, LinkedIn) -->
    <meta property="og:title" content="<?= $title ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:image" content="<?= $image ?>">
    <meta property="og:url" content="<?= $url ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $siteName ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title ?>">
    <meta name="twitter:description" content="<?= $description ?>">
    <meta name="twitter:image" content="<?= $image ?>">

    <!-- Styles -->
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <a href="/" class="logo"><?= $siteName ?></a>
            <ul class="nav-menu">
                <li><a href="/">Accueil</a></li>
                <li><a href="/categorie/conflit-militaire">Conflit</a></li>
                <li><a href="/categorie/humanitaire">Humanitaire</a></li>
                <li><a href="/categorie/chronologie">Chronologie</a></li>
                <li><a href="/categorie/reactions-internationales">International</a></li>
                <li><a href="/categorie/economie">Economie</a></li>
            </ul>
        </nav>
    </header>
    <main>
