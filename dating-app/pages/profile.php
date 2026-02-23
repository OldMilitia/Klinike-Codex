<?php
/**
 * Mi perfil - Edición
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';
require_once __DIR__ . '/../includes/image.php';
requireAuth();

$userId = currentUserId();
$user = Auth::currentUser();
$photos = User::getPhotos($userId);
$errors = [];

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token de seguridad inválido.";
    } else {
        if ($_POST['action'] === 'update_profile') {
            User::updateProfile($userId, [
                'name' => $_POST['name'] ?? '',
                'city' => $_POST['city'] ?? '',
                'bio' => $_POST['bio'] ?? '',
                'looking_for' => $_POST['looking_for'] ?? 'both',
            ]);
            setFlash('success', 'Perfil actualizado correctamente.');
            redirect('pages/profile.php');
        }

        if ($_POST['action'] === 'upload_photo' && isset($_FILES['photo'])) {
            $photoCount = User::countPhotos($userId);
            if ($photoCount >= MAX_PHOTOS_PER_USER) {
                setFlash('error', 'Has alcanzado el límite de ' . MAX_PHOTOS_PER_USER . ' fotos.');
            } else {
                $result = processUploadedImage($_FILES['photo']);
                if ($result) {
                    $isPrimary = empty($user['profile_photo']);
                    User::addPhoto($userId, $result['filename'], $result['thumbnail'], $isPrimary);
                    if ($isPrimary) {
                        User::updateProfilePhoto($userId, $result['filename']);
                    }
                    setFlash('success', 'Foto subida correctamente.');
                } else {
                    setFlash('error', 'Error al subir la imagen. Verifica el formato (JPG, PNG, WebP) y tamaño (máx. 2MB).');
                }
            }
            redirect('pages/profile.php');
        }

        if ($_POST['action'] === 'set_primary' && isset($_POST['photo_filename'])) {
            User::updateProfilePhoto($userId, $_POST['photo_filename']);
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE user_photos SET is_primary = 0 WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stmt = $db->prepare("UPDATE user_photos SET is_primary = 1 WHERE user_id = ? AND filename = ?");
            $stmt->execute([$userId, $_POST['photo_filename']]);
            setFlash('success', 'Foto de perfil actualizada.');
            redirect('pages/profile.php');
        }
    }
}

$countries = getCountries();
$pageTitle = 'Mi Perfil';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="fade-in">
    <div class="profile-header">
        <img src="<?= profilePhotoUrl($user['profile_photo']) ?>"
             alt="<?= sanitize($user['name']) ?>"
             class="profile-avatar">
        <div class="profile-info">
            <h1><?= sanitize($user['name']) ?></h1>
            <p class="profile-meta">
                <?= calculateAge($user['birthdate']) ?> años &bull;
                <?= getCountryName($user['country']) ?>
                <?= $user['city'] ? ', ' . sanitize($user['city']) : '' ?>
            </p>
            <p class="profile-meta">Miembro desde <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>

    <!-- Fotos -->
    <div class="profile-section">
        <h2>Mis fotos (<?= count($photos) ?>/<?= MAX_PHOTOS_PER_USER ?>)</h2>

        <div class="photo-grid">
            <?php foreach ($photos as $photo): ?>
            <div style="position: relative;">
                <img src="<?= profilePhotoUrl($photo['filename']) ?>"
                     alt="Foto"
                     title="<?= $photo['is_primary'] ? 'Foto principal' : 'Clic derecho para opciones' ?>">
                <?php if (!$photo['is_primary']): ?>
                <form method="POST" style="position:absolute;bottom:4px;right:4px;">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="set_primary">
                    <input type="hidden" name="photo_filename" value="<?= sanitize($photo['filename']) ?>">
                    <button type="submit" class="btn btn-sm btn-primary" style="width:auto;padding:0.2rem 0.5rem;font-size:0.7rem;">
                        &#9733; Principal
                    </button>
                </form>
                <?php else: ?>
                <span style="position:absolute;bottom:4px;right:4px;background:var(--primary);color:white;padding:0.15rem 0.4rem;border-radius:4px;font-size:0.7rem;">
                    &#9733; Principal
                </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php if (count($photos) < MAX_PHOTOS_PER_USER): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="upload_photo">
                <label class="photo-upload-area" style="aspect-ratio: 1; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                           style="display: none;" onchange="this.form.submit()">
                    <span style="font-size: 2rem;">+</span>
                    <span style="font-size: 0.8rem;">Agregar foto</span>
                </label>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Editar perfil -->
    <div class="profile-section">
        <h2>Editar perfil</h2>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= sanitize($user['name']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">Ciudad</label>
                    <input type="text" id="city" name="city" class="form-control"
                           value="<?= sanitize($user['city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="looking_for">Busco</label>
                    <select id="looking_for" name="looking_for" class="form-control">
                        <option value="male" <?= $user['looking_for'] === 'male' ? 'selected' : '' ?>>Hombres</option>
                        <option value="female" <?= $user['looking_for'] === 'female' ? 'selected' : '' ?>>Mujeres</option>
                        <option value="both" <?= $user['looking_for'] === 'both' ? 'selected' : '' ?>>Ambos</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="bio">Sobre mí</label>
                <textarea id="bio" name="bio" class="form-control"
                          placeholder="Cuéntanos un poco sobre ti..."><?= sanitize($user['bio'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
