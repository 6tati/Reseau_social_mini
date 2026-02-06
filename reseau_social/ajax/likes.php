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

$publicationId = (int)($_POST['publication_id'] ?? 0);
if ($publicationId === 0) {
    echo json_encode(['error' => 'ID de publication invalide']);
    exit;
}

////Verifie que si dejà like
$check = $pdo->prepare("SELECT 1 FROM likes WHERE publication_id = ? AND utilisateur_id = ?");
$check->execute([$publicationId, $userId]);

if ($check->fetch()) {
    ///retirer le like
    $pdo->prepare("DELETE FROM likes WHERE publication_id = ? AND utilisateur_id = ?")
        ->execute([$publicationId, $userId]);
    $liked = false;
} else {
    ////ajouter le like
    $pdo->prepare("INSERT INTO likes (publication_id, utilisateur_id) VALUES (?, ?)")
        ->execute([$publicationId, $userId]);
    $liked = true;

    //notification pour l'auteur de la publication
    $author = $pdo->prepare("SELECT utilisateur_id FROM publications WHERE id = ?");
    $author->execute([$publicationId]);
    $pub = $author->fetch();
    if ($pub && $pub['utilisateur_id'] !== $userId) {
        $pdo->prepare("INSERT INTO notifications (utilisateur_id, type, source_id) VALUES (?, 'like', ?)")
            ->execute([$pub['utilisateur_id'], $userId]);
    }
}

// Nouveau compteur
$count = $pdo->prepare("SELECT COUNT(*) as cnt FROM likes WHERE publication_id = ?");
$count->execute([$publicationId]);
$nbLikes = $count->fetch()['cnt'];

echo json_encode(['liked' => $liked, 'count' => $nbLikes]);
?>
