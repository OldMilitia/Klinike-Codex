<?php
/**
 * API - Subir foto de perfil
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';
require_once __DIR__ . '/../includes/image.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

if (!isset($_FILES['photo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibió ninguna foto']);
    exit;
}

$userId = currentUserId();

// Verificar límite de fotos
$photoCount = User::countPhotos($userId);
if ($photoCount >= MAX_PHOTOS_PER_USER) {
    http_response_code(400);
    echo json_encode(['error' => 'Has alcanzado el límite de ' . MAX_PHOTOS_PER_USER . ' fotos.']);
    exit;
}

// Procesar imagen
$result = processUploadedImage($_FILES['photo']);

if (!$result) {
    http_response_code(400);
    echo json_encode(['error' => 'Error al procesar la imagen. Verifica formato (JPG, PNG, WebP) y tamaño (máx. 2MB).']);
    exit;
}

// Si es la primera foto, hacerla principal
$user = Auth::currentUser();
$isPrimary = empty($user['profile_photo']);

User::addPhoto($userId, $result['filename'], $result['thumbnail'], $isPrimary);

if ($isPrimary) {
    User::updateProfilePhoto($userId, $result['filename']);
}

echo json_encode([
    'success' => true,
    'filename' => $result['filename'],
    'thumbnail' => $result['thumbnail']
]);
