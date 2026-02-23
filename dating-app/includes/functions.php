<?php
/**
 * Funciones utilitarias generales
 */

/**
 * Sanitizar entrada de texto
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirigir a una URL
 */
function redirect(string $path): void {
    header("Location: " . BASE_URL . "/" . ltrim($path, '/'));
    exit;
}

/**
 * Verificar si el usuario está logueado
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Obtener ID del usuario actual
 */
function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Requerir autenticación
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        redirect('pages/login.php');
    }
}

/**
 * Mostrar mensaje flash
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Calcular edad a partir de fecha de nacimiento
 */
function calculateAge(string $birthdate): int {
    $birth = new DateTime($birthdate);
    $today = new DateTime();
    return $birth->diff($today)->y;
}

/**
 * Generar token CSRF
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validar token CSRF
 */
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Obtener la lista de países permitidos
 */
function getCountries(): array {
    return json_decode(ALLOWED_COUNTRIES, true);
}

/**
 * Obtener nombre del país por código
 */
function getCountryName(string $code): string {
    $countries = getCountries();
    return $countries[$code] ?? $code;
}

/**
 * Formatear fecha para mostrar
 */
function formatDate(string $datetime): string {
    $date = new DateTime($datetime);
    $now = new DateTime();
    $diff = $now->diff($date);

    if ($diff->days === 0) {
        if ($diff->h === 0) {
            return $diff->i <= 1 ? "Justo ahora" : "Hace {$diff->i} min";
        }
        return "Hace {$diff->h}h";
    }
    if ($diff->days === 1) return "Ayer";
    if ($diff->days < 7) return "Hace {$diff->days} días";

    return $date->format('d/m/Y');
}

/**
 * URL de foto de perfil (con fallback)
 */
function profilePhotoUrl(?string $filename): string {
    if ($filename && file_exists(PROFILES_PATH . $filename)) {
        return BASE_URL . '/uploads/profiles/' . $filename;
    }
    return BASE_URL . '/assets/img/default-avatar.svg';
}

/**
 * URL de thumbnail
 */
function thumbnailUrl(?string $filename): string {
    if ($filename && file_exists(THUMBNAILS_PATH . $filename)) {
        return BASE_URL . '/uploads/thumbnails/' . $filename;
    }
    return profilePhotoUrl($filename);
}
