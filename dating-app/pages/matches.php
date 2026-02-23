<?php
/**
 * Lista de matches
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';
requireAuth();

$userId = currentUserId();
$matches = User::getMatches($userId);

$pageTitle = 'Mis Matches';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="fade-in">
    <h1 style="margin-bottom: 1.5rem;">Mis Matches</h1>

    <?php if (empty($matches)): ?>
    <div class="no-profiles">
        <h2>Aún no tienes matches</h2>
        <p>Sigue explorando perfiles y dando likes para encontrar tu match.</p>
        <a href="<?= BASE_URL ?>/pages/discover.php" class="btn btn-primary" style="width: auto; margin-top: 1rem; display: inline-flex;">Descubrir personas</a>
    </div>
    <?php else: ?>
    <div class="matches-grid">
        <?php foreach ($matches as $match): ?>
        <a href="<?= BASE_URL ?>/pages/chat.php?match_id=<?= $match['match_id'] ?>" class="match-card">
            <img src="<?= profilePhotoUrl($match['profile_photo']) ?>"
                 alt="<?= sanitize($match['name']) ?>"
                 class="match-card-avatar">
            <div class="match-card-info">
                <div class="match-card-name">
                    <?= sanitize($match['name']) ?>, <?= $match['age'] ?>
                    <span style="font-size: 0.8rem; color: var(--text-light); font-weight: normal;">
                        &bull; <?= $match['country_name'] ?>
                    </span>
                </div>
                <?php if ($match['last_message']): ?>
                <div class="match-card-preview">
                    <?php if ((int)$match['last_message']['sender_id'] === $userId): ?>
                    <span style="color: var(--text-muted);">Tú: </span>
                    <?php endif; ?>
                    <?= sanitize(mb_substr($match['last_message']['message'], 0, 50)) ?>
                </div>
                <div class="match-card-time"><?= formatDate($match['last_message']['created_at']) ?></div>
                <?php else: ?>
                <div class="match-card-preview" style="color: var(--primary);">&#128075; ¡Envía el primer mensaje!</div>
                <div class="match-card-time"><?= formatDate($match['matched_at']) ?></div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
