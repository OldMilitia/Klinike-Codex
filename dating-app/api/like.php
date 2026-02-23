<?php
/**
 * API - Dar like a un perfil
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';

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

$input = json_decode(file_get_contents('php://input'), true);
$toUserId = (int)($input['user_id'] ?? 0);

if ($toUserId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario inválido']);
    exit;
}

$result = User::like(currentUserId(), $toUserId);

if ($result['is_match'] ?? false) {
    $otherUser = User::getById($toUserId);
    $result['user_name'] = $otherUser['name'] ?? '';
}

echo json_encode($result);
