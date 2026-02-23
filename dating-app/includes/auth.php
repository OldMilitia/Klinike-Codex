<?php
/**
 * Sistema de autenticación
 */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

class Auth {
    /**
     * Registrar nuevo usuario
     */
    public static function register(array $data): array {
        $errors = [];
        $db = Database::getConnection();

        // Validaciones
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $errors[] = "El nombre debe tener al menos 2 caracteres.";
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Ingresa un correo electrónico válido.";
        }

        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres.";
        }

        if ($data['password'] !== ($data['password_confirm'] ?? '')) {
            $errors[] = "Las contraseñas no coinciden.";
        }

        if (empty($data['birthdate'])) {
            $errors[] = "La fecha de nacimiento es obligatoria.";
        } else {
            $age = calculateAge($data['birthdate']);
            if ($age < MIN_AGE) {
                $errors[] = "Debes tener al menos " . MIN_AGE . " años.";
            }
        }

        if (empty($data['gender']) || !in_array($data['gender'], ['male', 'female'])) {
            $errors[] = "Selecciona tu género.";
        }

        $countries = getCountries();
        if (empty($data['country']) || !isset($countries[$data['country']])) {
            $errors[] = "Selecciona un país válido.";
        }

        // Verificar email duplicado
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                $errors[] = "Este correo electrónico ya está registrado.";
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Insertar usuario
        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, name, birthdate, gender, country, city, looking_for)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $lookingFor = $data['gender'] === 'female' ? 'male' : 'female';

        $stmt->execute([
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            sanitize($data['name']),
            $data['birthdate'],
            $data['gender'],
            $data['country'],
            sanitize($data['city'] ?? ''),
            $lookingFor
        ]);

        $userId = (int)$db->lastInsertId();

        // Iniciar sesión automáticamente
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $data['name'];

        return ['success' => true, 'user_id' => $userId];
    }

    /**
     * Iniciar sesión
     */
    public static function login(string $email, string $password): array {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT id, name, password_hash, is_active FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'errors' => ['Correo o contraseña incorrectos.']];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'errors' => ['Tu cuenta ha sido desactivada.']];
        }

        // Actualizar última actividad
        $stmt = $db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Guardar en sesión
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];

        return ['success' => true, 'user_id' => (int)$user['id']];
    }

    /**
     * Cerrar sesión
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Obtener datos del usuario actual
     */
    public static function currentUser(): ?array {
        if (!isLoggedIn()) return null;

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([currentUserId()]);
        return $stmt->fetch() ?: null;
    }
}
