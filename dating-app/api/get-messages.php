<?php
/**
 * API - Obtener mensajes de un match (para polling)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messages.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$matchId = (int)($_GET['match_id'] ?? 0);

if ($matchId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de match inválido']);
    exit;
}

$messages = Messages::getByMatch($matchId, currentUserId());

// Marcar como leídos
Messages::markAsRead($matchId, currentUserId());

echo json_encode(['messages' => $messages]);
