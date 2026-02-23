<?php
/**
 * API - Enviar mensaje
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messages.php';

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
$matchId = (int)($input['match_id'] ?? 0);
$message = $input['message'] ?? '';

if ($matchId <= 0 || empty(trim($message))) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$result = Messages::send($matchId, currentUserId(), $message);

if (!$result['success']) {
    http_response_code(400);
}

echo json_encode($result);
