<?php
/**
 * Ver perfil de otro usuario
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';
requireAuth();

$profileId = (int)($_GET['id'] ?? 0);
if ($profileId <= 0) {
    redirect('pages/discover.php');
}

$profile = User::getById($profileId);
if (!$profile) {
    setFlash('error', 'Perfil no encontrado.');
    redirect('pages/discover.php');
}

$pageTitle = $profile['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<meta name="base-url" content="<?= BASE_URL ?>">

<div class="view-profile fade-in">
    <a href="<?= BASE_URL ?>/pages/discover.php" style="color: var(--text-light); display: inline-block; margin-bottom: 1rem;">&larr; Volver a descubrir</a>

    <img src="<?= profilePhotoUrl($profile['profile_photo']) ?>"
         alt="<?= sanitize($profile['name']) ?>"
         class="view-profile-photo">

    <div class="view-profile-header">
        <div>
            <h1><?= sanitize($profile['name']) ?> <span><?= $profile['age'] ?></span></h1>
            <p style="color: var(--text-light);">
                &#128205; <?= sanitize($profile['city'] ?: $profile['country_name']) ?>, <?= $profile['country_name'] ?>
            </p>
        </div>
    </div>

    <?php if (!empty($profile['bio'])): ?>
    <div class="profile-section">
        <h2>Sobre mí</h2>
        <p><?= nl2br(sanitize($profile['bio'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($profile['photos'])): ?>
    <div class="profile-section">
        <h2>Fotos</h2>
        <div class="photo-grid">
            <?php foreach ($profile['photos'] as $photo): ?>
            <img src="<?= profilePhotoUrl($photo['filename']) ?>" alt="Foto">
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($profileId !== currentUserId()): ?>
    <div style="display: flex; justify-content: center; gap: 2rem; margin: 2rem 0;">
        <input type="hidden" id="current-profile-id" value="<?= $profile['id'] ?>">
        <button class="btn btn-pass" onclick="handlePass()" title="Pasar">&#10007;</button>
        <button class="btn btn-like" onclick="handleLike()" title="Me gusta">&#10084;</button>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
