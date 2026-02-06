<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$userId = requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../messages.php');
    exit;
}

$targetId = (int)($_POST['target_id'] ?? 0);
$message  = trim($_POST['message'] ?? '');

if ($targetId === 0 || empty($message) || $targetId === $userId) {
    header('Location: ../messages.php');
    exit;
}

/////Insertion dumessage
$pdo->prepare("INSERT INTO messages_prives (expediteur_id, destinataire_id, message) VALUES (?, ?, ?)")
    ->execute([$userId, $targetId, $message]);

///Notification
$pdo->prepare("INSERT INTO notifications (utilisateur_id, type, source_id) VALUES (?, 'message', ?)")
    ->execute([$targetId, $userId]);

//////Redirige vers la même conversation
header("Location: ../messages.php?user=$targetId");
exit;
?>
