<?php
/**
 * LatinMatch - Configuración principal
 * Renombrar a config.php y ajustar los valores
 */

// Modo desarrollo
define('DEBUG_MODE', true);

// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'latinmatch_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// URL base de la app (sin slash al final)
define('BASE_URL', 'http://localhost/dating-app');

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('PROFILES_PATH', UPLOADS_PATH . 'profiles/');
define('THUMBNAILS_PATH', UPLOADS_PATH . 'thumbnails/');

// Configuración de imágenes
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024); // 2MB
define('THUMB_WIDTH', 300);
define('THUMB_HEIGHT', 300);
define('PROFILE_MAX_WIDTH', 800);
define('PROFILE_MAX_HEIGHT', 800);
define('JPEG_QUALITY', 75);
define('MAX_PHOTOS_PER_USER', 6);

// Países permitidos
define('ALLOWED_COUNTRIES', json_encode([
    'VE' => 'Venezuela',
    'PE' => 'Perú',
    'PY' => 'Paraguay',
    'BO' => 'Bolivia',
    'CL' => 'Chile',
    'AR' => 'Argentina',
    'UY' => 'Uruguay'
]));

// Edad mínima y máxima
define('MIN_AGE', 18);
define('MAX_AGE', 65);

// Sesión
define('SESSION_LIFETIME', 86400 * 30); // 30 días

// Zona horaria
date_default_timezone_set('America/Caracas');

// Manejo de errores
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
