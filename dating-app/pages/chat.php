<?php
/**
 * Chat con un match
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/user.php';
require_once __DIR__ . '/../includes/messages.php';
requireAuth();

$userId = currentUserId();
$matchId = (int)($_GET['match_id'] ?? 0);

if ($matchId <= 0) {
    redirect('pages/matches.php');
}

// Obtener info del match y el otro usuario
$db = Database::getConnection();
$stmt = $db->prepare("
    SELECT m.*,
           CASE WHEN m.user1_id = ? THEN m.user2_id ELSE m.user1_id END as other_user_id
    FROM matches m
    WHERE m.id = ? AND (m.user1_id = ? OR m.user2_id = ?)
");
$stmt->execute([$userId, $matchId, $userId, $userId]);
$match = $stmt->fetch();

if (!$match) {
    setFlash('error', 'Conversación no encontrada.');
    redirect('pages/matches.php');
}

$otherUser = User::getById((int)$match['other_user_id']);
if (!$otherUser) {
    redirect('pages/matches.php');
}

// Obtener mensajes
$messages = Messages::getByMatch($matchId, $userId);

// Marcar como leídos
Messages::markAsRead($matchId, $userId);

$pageTitle = 'Chat con ' . $otherUser['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<meta name="base-url" content="<?= BASE_URL ?>">
<input type="hidden" id="current-user-id" value="<?= $userId ?>">

<div class="chat-container fade-in">
    <div class="chat-header">
        <a href="<?= BASE_URL ?>/pages/matches.php" class="back-link">&larr;</a>
        <img src="<?= profilePhotoUrl($otherUser['profile_photo']) ?>"
             alt="<?= sanitize($otherUser['name']) ?>">
        <div>
            <h3><?= sanitize($otherUser['name']) ?></h3>
            <span style="font-size: 0.8rem; color: var(--text-light);">
                <?= $otherUser['country_name'] ?>
            </span>
        </div>
        <a href="<?= BASE_URL ?>/pages/view-profile.php?id=<?= $otherUser['id'] ?>"
           style="margin-left: auto; font-size: 0.85rem;" class="btn btn-outline btn-sm">
            Ver perfil
        </a>
    </div>

    <div class="chat-messages" id="chat-messages">
        <?php if (empty($messages)): ?>
        <div style="text-align: center; color: var(--text-muted); padding: 2rem;">
            <p>&#128075; ¡Envía el primer mensaje!</p>
        </div>
        <?php endif; ?>

        <?php foreach ($messages as $msg): ?>
        <div class="message <?= (int)$msg['sender_id'] === $userId ? 'message-sent' : 'message-received' ?>">
            <?= sanitize($msg['message']) ?>
            <div class="message-time"><?= formatDate($msg['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <form class="chat-input" id="chat-form">
        <input type="hidden" name="match_id" value="<?= $matchId ?>">
        <input type="text" name="message" placeholder="Escribe un mensaje..." autocomplete="off" autofocus>
        <button type="submit">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
