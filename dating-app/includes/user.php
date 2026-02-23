<?php
/**
 * Modelo de usuario - Perfiles y descubrimiento
 */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

class User {
    /**
     * Obtener perfil por ID
     */
    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, name, birthdate, gender, country, city, bio, profile_photo,
                   looking_for, is_verified, last_active, created_at
            FROM users WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $user['age'] = calculateAge($user['birthdate']);
            $user['country_name'] = getCountryName($user['country']);
            $user['photos'] = self::getPhotos($id);
        }

        return $user ?: null;
    }

    /**
     * Actualizar perfil
     */
    public static function updateProfile(int $userId, array $data): bool {
        $db = Database::getConnection();
        $fields = [];
        $values = [];

        $allowed = ['name', 'city', 'bio', 'looking_for'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = sanitize($data[$field]);
            }
        }

        if (empty($fields)) return false;

        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Actualizar foto de perfil principal
     */
    public static function updateProfilePhoto(int $userId, string $filename): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        return $stmt->execute([$filename, $userId]);
    }

    /**
     * Obtener fotos del usuario
     */
    public static function getPhotos(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM user_photos WHERE user_id = ? ORDER BY is_primary DESC, created_at ASC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Agregar foto
     */
    public static function addPhoto(int $userId, string $filename, string $thumbnail, bool $isPrimary = false): int {
        $db = Database::getConnection();

        // Si es primaria, quitar la actual
        if ($isPrimary) {
            $stmt = $db->prepare("UPDATE user_photos SET is_primary = 0 WHERE user_id = ?");
            $stmt->execute([$userId]);
        }

        $stmt = $db->prepare("
            INSERT INTO user_photos (user_id, filename, thumbnail, is_primary)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $filename, $thumbnail, $isPrimary ? 1 : 0]);
        return (int)$db->lastInsertId();
    }

    /**
     * Contar fotos del usuario
     */
    public static function countPhotos(int $userId): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM user_photos WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Descubrir perfiles (sistema de matching por país)
     * Mujeres venezolanas ven hombres de otros países y viceversa
     */
    public static function discover(int $userId, int $page = 1, int $limit = 20): array {
        $db = Database::getConnection();
        $currentUser = self::getById($userId);
        if (!$currentUser) return [];

        $offset = ($page - 1) * $limit;

        // Obtener IDs ya vistos (likes dados)
        $stmt = $db->prepare("SELECT to_user_id FROM likes WHERE from_user_id = ?");
        $stmt->execute([$userId]);
        $seenIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $seenIds[] = $userId; // excluirse a sí mismo

        $placeholders = implode(',', array_fill(0, count($seenIds), '?'));

        // Lógica de matching:
        // - Si es mujer venezolana -> mostrar hombres de otros países
        // - Si es hombre de otro país -> mostrar mujeres venezolanas
        // - Otros casos -> mostrar el género opuesto
        $params = $seenIds;

        if ($currentUser['gender'] === 'female' && $currentUser['country'] === 'VE') {
            $sql = "SELECT id, name, birthdate, gender, country, city, bio, profile_photo, last_active
                    FROM users
                    WHERE id NOT IN ($placeholders)
                    AND is_active = 1
                    AND gender = 'male'
                    AND country != 'VE'
                    ORDER BY last_active DESC
                    LIMIT ? OFFSET ?";
        } elseif ($currentUser['gender'] === 'male' && $currentUser['country'] !== 'VE') {
            $sql = "SELECT id, name, birthdate, gender, country, city, bio, profile_photo, last_active
                    FROM users
                    WHERE id NOT IN ($placeholders)
                    AND is_active = 1
                    AND gender = 'female'
                    AND country = 'VE'
                    ORDER BY last_active DESC
                    LIMIT ? OFFSET ?";
        } else {
            // Caso general: mostrar género buscado
            $lookingFor = $currentUser['looking_for'];
            $genderFilter = $lookingFor === 'both' ? "1=1" : "gender = ?";
            $sql = "SELECT id, name, birthdate, gender, country, city, bio, profile_photo, last_active
                    FROM users
                    WHERE id NOT IN ($placeholders)
                    AND is_active = 1
                    AND $genderFilter
                    ORDER BY last_active DESC
                    LIMIT ? OFFSET ?";
            if ($lookingFor !== 'both') {
                $params[] = $lookingFor;
            }
        }

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $profiles = $stmt->fetchAll();

        // Enriquecer datos
        foreach ($profiles as &$profile) {
            $profile['age'] = calculateAge($profile['birthdate']);
            $profile['country_name'] = getCountryName($profile['country']);
        }

        return $profiles;
    }

    /**
     * Dar like a un perfil
     */
    public static function like(int $fromUserId, int $toUserId): array {
        $db = Database::getConnection();

        // Verificar que no se dé like a sí mismo
        if ($fromUserId === $toUserId) {
            return ['success' => false, 'error' => 'No puedes darte like a ti mismo.'];
        }

        // Insertar like (ignorar duplicados)
        $stmt = $db->prepare("INSERT IGNORE INTO likes (from_user_id, to_user_id) VALUES (?, ?)");
        $stmt->execute([$fromUserId, $toUserId]);

        // Verificar si hay match (like mutuo)
        $stmt = $db->prepare("SELECT id FROM likes WHERE from_user_id = ? AND to_user_id = ?");
        $stmt->execute([$toUserId, $fromUserId]);
        $isMatch = (bool)$stmt->fetch();

        if ($isMatch) {
            // Crear match (ordenar IDs para evitar duplicados)
            $user1 = min($fromUserId, $toUserId);
            $user2 = max($fromUserId, $toUserId);
            $stmt = $db->prepare("INSERT IGNORE INTO matches (user1_id, user2_id) VALUES (?, ?)");
            $stmt->execute([$user1, $user2]);
        }

        return ['success' => true, 'is_match' => $isMatch];
    }

    /**
     * Pasar/rechazar perfil (no guardar nada, simplemente no dar like)
     * Se registra como like con valor negativo para no volver a mostrarlo
     */
    public static function pass(int $fromUserId, int $toUserId): bool {
        // Por ahora, simplemente registrar un like "invisible" para no repetir
        // En una versión futura se podría tener tabla separada de "passes"
        return true;
    }

    /**
     * Obtener matches del usuario
     */
    public static function getMatches(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT m.id as match_id, m.created_at as matched_at,
                   u.id, u.name, u.profile_photo, u.country, u.last_active,
                   u.birthdate
            FROM matches m
            JOIN users u ON (u.id = CASE WHEN m.user1_id = ? THEN m.user2_id ELSE m.user1_id END)
            WHERE (m.user1_id = ? OR m.user2_id = ?)
            AND u.is_active = 1
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$userId, $userId, $userId]);
        $matches = $stmt->fetchAll();

        foreach ($matches as &$match) {
            $match['age'] = calculateAge($match['birthdate']);
            $match['country_name'] = getCountryName($match['country']);
            // Obtener último mensaje
            $stmtMsg = $db->prepare("
                SELECT message, created_at, sender_id
                FROM messages
                WHERE match_id = ?
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmtMsg->execute([$match['match_id']]);
            $match['last_message'] = $stmtMsg->fetch() ?: null;
        }

        return $matches;
    }
}
