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

$action   = $_POST['action'] ?? '';
$targetId = (int)($_POST['target_id'] ?? 0);

if ($targetId === 0 || $targetId === $userId) {
    echo json_encode(['error' => 'ID invalide']);
    exit;
}

switch ($action) {

//Envoyer une demande d'ami
    case 'send':
        //Vérifie que deja connue
        $check = $pdo->prepare("
            SELECT 1 FROM amis
            WHERE (utilisateur_id = ? AND ami_id = ?)
               OR (utilisateur_id = ? AND ami_id = ?)
        ");
        $check->execute([$userId, $targetId, $targetId, $userId]);
        if ($check->fetch()) {
            echo json_encode(['error' => 'Demande déjà envoyée ou déjà ami.']);
            exit;
        }
        $pdo->prepare("INSERT INTO amis (utilisateur_id, ami_id, statut) VALUES (?, ?, 'en_attente')")
            ->execute([$userId, $targetId]);

        // Notification pour la cible
        $pdo->prepare("INSERT INTO notifications (utilisateur_id, type, source_id) VALUES (?, 'ami', ?)")
            ->execute([$targetId, $userId]);

        echo json_encode(['success' => true, 'message' => 'Demande envoyée']);
        break;

    ////Accepter une demande
    case 'accepte':
        $pdo->prepare("UPDATE amis SET statut = 'accepte' WHERE utilisateur_id = ? AND ami_id = ? AND statut = 'en_attente'")
            ->execute([$targetId, $userId]);

        //   notification "accepté"
        $pdo->prepare("INSERT INTO notifications (utilisateur_id, type, source_id) VALUES (?, 'ami', ?)")
            ->execute([$targetId, $userId]);

        echo json_encode(['success' => true, 'message' => 'Ami accepté']);
        break;

    //Refuser une demande 
    case 'refuse':
        $pdo->prepare("DELETE FROM amis WHERE utilisateur_id = ? AND ami_id = ? AND statut = 'en_attente'")
            ->execute([$targetId, $userId]);
        echo json_encode(['success' => true, 'message' => 'Demande refusée']);
        break;

    //ssupprimer un ami
    case 'remove':
        $pdo->prepare("DELETE FROM amis WHERE (utilisateur_id = ? AND ami_id = ?) OR (utilisateur_id = ? AND ami_id = ?)")
            ->execute([$userId, $targetId, $targetId, $userId]);
        echo json_encode(['success' => true, 'message' => 'Ami supprimé']);
        break;

    default:
        echo json_encode(['error' => 'Action inconnue']);
}
?>
