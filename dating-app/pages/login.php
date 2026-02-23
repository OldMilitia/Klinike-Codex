<?php
/**
 * Inicio de sesión
 */
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    redirect('pages/discover.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Token de seguridad inválido. Intenta de nuevo.";
    } else {
        $result = Auth::login(
            trim($_POST['email'] ?? ''),
            $_POST['password'] ?? ''
        );

        if ($result['success']) {
            redirect('pages/discover.php');
        }

        $errors = $result['errors'];
    }
}

$pageTitle = 'Iniciar sesión';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card fade-in">
    <h1 class="form-title">Iniciar sesión</h1>
    <p class="form-subtitle">Bienvenido/a de vuelta</p>

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
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="tu@correo.com" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="Tu contraseña" required>
        </div>

        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <div class="form-footer">
        ¿No tienes cuenta? <a href="<?= BASE_URL ?>/pages/register.php">Regístrate gratis</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
