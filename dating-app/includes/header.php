<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$flash = getFlash();
$unreadCount = 0;
if (isLoggedIn()) {
    require_once __DIR__ . '/messages.php';
    $unreadCount = Messages::countUnread(currentUserId());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'LatinMatch' ?> - Conectando corazones latinos</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?= BASE_URL ?>/" class="nav-logo">
                <span class="logo-icon">&#10084;</span> LatinMatch
            </a>

            <?php if (isLoggedIn()): ?>
            <div class="nav-links">
                <a href="<?= BASE_URL ?>/pages/discover.php" class="nav-link" title="Descubrir">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                </a>
                <a href="<?= BASE_URL ?>/pages/matches.php" class="nav-link" title="Matches">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <?php if ($unreadCount > 0): ?>
                    <span class="badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/pages/profile.php" class="nav-link" title="Mi Perfil">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </a>
                <a href="<?= BASE_URL ?>/api/logout.php" class="nav-link" title="Salir">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
        <?= sanitize($flash['message']) ?>
        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php endif; ?>

    <main class="main-content">
