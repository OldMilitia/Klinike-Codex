<?php
/**
 * Registro de nuevos usuarios
 */
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    redirect('pages/discover.php');
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token de seguridad inválido. Intenta de nuevo.";
    } else {
        $formData = $_POST;
        $result = Auth::register($_POST);

        if ($result['success']) {
            setFlash('success', '¡Bienvenido/a a LatinMatch! Completa tu perfil para empezar.');
            redirect('pages/profile.php');
        }

        $errors = $result['errors'];
    }
}

$countries = getCountries();
$pageTitle = 'Registro';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card fade-in">
    <h1 class="form-title">Crear cuenta</h1>
    <p class="form-subtitle">Únete gratis y encuentra tu conexión latina</p>

    <?php if (!empty($errors)): ?>
    <ul class="error-list">
        <?php foreach ($errors as $error): ?>
        <li><?= sanitize($error) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-group">
            <label for="name">Nombre</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?= sanitize($formData['name'] ?? '') ?>"
                   placeholder="Tu nombre" required minlength="2">
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= sanitize($formData['email'] ?? '') ?>"
                   placeholder="tu@correo.com" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Mínimo 6 caracteres" required minlength="6">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmar</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                       placeholder="Repite la contraseña" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="birthdate">Fecha de nacimiento</label>
                <input type="date" id="birthdate" name="birthdate" class="form-control"
                       value="<?= sanitize($formData['birthdate'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="gender">Género</label>
                <select id="gender" name="gender" class="form-control" required>
                    <option value="">Seleccionar...</option>
                    <option value="female" <?= ($formData['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Mujer</option>
                    <option value="male" <?= ($formData['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Hombre</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="country">País</label>
                <select id="country" name="country" class="form-control" required>
                    <option value="">Seleccionar...</option>
                    <?php foreach ($countries as $code => $name): ?>
                    <option value="<?= $code ?>" <?= ($formData['country'] ?? '') === $code ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" class="form-control"
                       value="<?= sanitize($formData['city'] ?? '') ?>"
                       placeholder="Tu ciudad">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Crear mi cuenta</button>
    </form>

    <div class="form-footer">
        ¿Ya tienes cuenta? <a href="<?= BASE_URL ?>/pages/login.php">Inicia sesión</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
