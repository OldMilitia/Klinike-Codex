<?php
/**
 * Sistema de mensajería
 */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

class Messages {
    /**
     * Enviar mensaje
     */
    public static function send(int $matchId, int $senderId, string $message): array {
        $db = Database::getConnection();

        // Verificar que el usuario es parte del match
        $stmt = $db->prepare("
            SELECT id FROM matches
            WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ");
        $stmt->execute([$matchId, $senderId, $senderId]);

        if (!$stmt->fetch()) {
            return ['success' => false, 'error' => 'No tienes acceso a esta conversación.'];
        }

        $message = trim($message);
        if (empty($message)) {
            return ['success' => false, 'error' => 'El mensaje no puede estar vacío.'];
        }

        if (strlen($message) > 2000) {
            return ['success' => false, 'error' => 'El mensaje es demasiado largo (máx. 2000 caracteres).'];
        }

        $stmt = $db->prepare("
            INSERT INTO messages (match_id, sender_id, message)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$matchId, $senderId, $message]);

        return [
            'success' => true,
            'message_id' => (int)$db->lastInsertId(),
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtener mensajes de un match
     */
    public static function getByMatch(int $matchId, int $userId, int $page = 1, int $limit = 50): array {
        $db = Database::getConnection();

        // Verificar acceso
        $stmt = $db->prepare("
            SELECT id FROM matches
            WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ");
        $stmt->execute([$matchId, $userId, $userId]);

        if (!$stmt->fetch()) {
            return [];
        }

        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("
            SELECT m.*, u.name as sender_name, u.profile_photo as sender_photo
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE m.match_id = ?
            ORDER BY m.created_at ASC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$matchId, $limit, $offset]);

        return $stmt->fetchAll();
    }

    /**
     * Marcar mensajes como leídos
     */
    public static function markAsRead(int $matchId, int $userId): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE messages SET is_read = 1
            WHERE match_id = ? AND sender_id != ? AND is_read = 0
        ");
        return $stmt->execute([$matchId, $userId]);
    }

    /**
     * Contar mensajes no leídos del usuario
     */
    public static function countUnread(int $userId): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM messages msg
            JOIN matches m ON m.id = msg.match_id
            WHERE (m.user1_id = ? OR m.user2_id = ?)
            AND msg.sender_id != ?
            AND msg.is_read = 0
        ");
        $stmt->execute([$userId, $userId, $userId]);
        return (int)$stmt->fetchColumn();
    }
}
