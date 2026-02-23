<?php
/**
 * LatinMatch - Página principal / Landing
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Si ya está logueado, redirigir a discover
if (isLoggedIn()) {
    redirect('pages/discover.php');
}

$pageTitle = 'Inicio';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1 class="hero-title">Conectando corazones latinos</h1>
    <p class="hero-subtitle">
        La plataforma para que mujeres venezolanas y hombres latinoamericanos
        encuentren el amor sin fronteras.
    </p>
    <div class="hero-buttons">
        <a href="<?= BASE_URL ?>/pages/register.php" class="btn btn-primary" style="width: auto;">Crear cuenta gratis</a>
        <a href="<?= BASE_URL ?>/pages/login.php" class="btn btn-outline">Iniciar sesión</a>
    </div>

    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">&#127758;</div>
            <h3>Conexión Latina</h3>
            <p>Conectamos Venezuela con Perú, Paraguay, Bolivia, Chile, Argentina y Uruguay.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128274;</div>
            <h3>Perfiles Verificados</h3>
            <p>Sistema de verificación para perfiles auténticos y seguros.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128172;</div>
            <h3>Chat Privado</h3>
            <p>Chatea solo con tus matches. Tu privacidad es lo primero.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">&#128247;</div>
            <h3>Fotos Optimizadas</h3>
            <p>Subida rápida con optimización automática de imágenes.</p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
