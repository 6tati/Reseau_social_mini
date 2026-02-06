<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$userId = requireLogin(false);
if ($userId === null) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? 'count';

switch ($action) {

    //Polling des notis
    case 'count':
        $notifs = $pdo->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE utilisateur_id = ? AND lu = 0");
        $notifs->execute([$userId]);
        $nCount = $notifs->fetch()['cnt'];

        $msgs = $pdo->prepare("SELECT COUNT(*) as cnt FROM messages_prives WHERE destinataire_id = ? AND lu = 0");
        $msgs->execute([$userId]);
        $mCount = $msgs->fetch()['cnt'];

        $friends = $pdo->prepare("SELECT COUNT(*) as cnt FROM amis WHERE ami_id = ? AND statut = 'en_attente'");
        $friends->execute([$userId]);
        $fCount = $friends->fetch()['cnt'];

        echo json_encode([
            'notifications' => $nCount,
            'messages'      => $mCount,
            'friends'       => $fCount
        ]);
        break;

    ///supprimer une notif
    case 'delete':
        $notifId = (int)($_POST['notif_id'] ?? 0);
        if ($notifId > 0) {
            $pdo->prepare("DELETE FROM notifications WHERE id = ? AND utilisateur_id = ?")
                ->execute([$notifId, $userId]);
        }
        echo json_encode(['success' => true]);
        break;

    ///effacer toutes les notifs
    case 'clear':
        $pdo->prepare("DELETE FROM notifications WHERE utilisateur_id = ?")
            ->execute([$userId]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Action inconnue']);
}
?>
