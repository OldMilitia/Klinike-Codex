<?php
/**
 * Descubrir perfiles - Sistema de matching
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';
requireAuth();

$userId = currentUserId();
$profiles = User::discover($userId, 1, 1); // Un perfil a la vez
$profile = $profiles[0] ?? null;

$pageTitle = 'Descubrir';
require_once __DIR__ . '/../includes/header.php';
?>

<meta name="base-url" content="<?= BASE_URL ?>">

<div class="discover-container fade-in">
    <?php if ($profile): ?>
    <input type="hidden" id="current-profile-id" value="<?= $profile['id'] ?>">

    <div class="profile-card" id="discover-card">
        <img src="<?= profilePhotoUrl($profile['profile_photo']) ?>"
             alt="<?= sanitize($profile['name']) ?>"
             class="profile-card-image"
             onclick="window.location='<?= BASE_URL ?>/pages/view-profile.php?id=<?= $profile['id'] ?>'">

        <div class="profile-card-info">
            <div class="profile-card-name">
                <?= sanitize($profile['name']) ?> <span><?= $profile['age'] ?></span>
            </div>
            <div class="profile-card-location">
                &#128205; <?= sanitize($profile['city'] ?: $profile['country_name']) ?>, <?= $profile['country_name'] ?>
            </div>
            <?php if (!empty($profile['bio'])): ?>
            <div class="profile-card-bio">
                <?= sanitize(mb_substr($profile['bio'], 0, 150)) ?><?= mb_strlen($profile['bio']) > 150 ? '...' : '' ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="profile-card-actions">
            <button class="btn btn-pass" onclick="handlePass()" title="Pasar">&#10007;</button>
            <button class="btn btn-like" onclick="handleLike()" title="Me gusta">&#10084;</button>
        </div>
    </div>

    <?php else: ?>
    <div class="no-profiles">
        <h2>No hay más perfiles por ahora</h2>
        <p>Vuelve más tarde para descubrir nuevas personas.</p>
        <a href="<?= BASE_URL ?>/pages/profile.php" class="btn btn-outline" style="margin-top: 1rem; display: inline-flex;">Completar mi perfil</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
