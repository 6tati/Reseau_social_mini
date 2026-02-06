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

$postId = (int)($_POST['post_id'] ?? 0);
if ($postId === 0) {
    echo json_encode(['error' => 'ID invalide']);
    exit;
}

//Vérifie que la publication appartient à l'utilisateur
$check = $pdo->prepare("SELECT utilisateur_id FROM publications WHERE id = ?");
$check->execute([$postId]);
$pub = $check->fetch();

if (!$pub || $pub['utilisateur_id'] !== $userId) {
    echo json_encode(['error' => 'Vous n\'êtes pas autorisé à supprimer cette publication']);
    exit;
}

//supprime
$pdo->prepare("DELETE FROM publications WHERE id = ?")
    ->execute([$postId]);

echo json_encode(['success' => true]);
?>
