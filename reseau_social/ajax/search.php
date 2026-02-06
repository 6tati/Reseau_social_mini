<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$userId = requireLogin(false);
if ($userId === null) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

//recherche LIKE sur pseudo et nom_complet
$stmt = $pdo->prepare("
    SELECT id, pseudo, nom_complet, photo_profil
    FROM utilisateurs
    WHERE id != ?
      AND (pseudo LIKE ? OR nom_complet LIKE ?)
    LIMIT 20
");
$pattern = '%' . $query . '%';
$stmt->execute([$userId, $pattern, $pattern]);
$users = $stmt->fetchAll();

//Enrichir avec l'état de relation
$results = [];
foreach ($users as $u) {
    $rel = $pdo->prepare("
        SELECT statut FROM amis
        WHERE (utilisateur_id = ? AND ami_id = ?)
           OR (utilisateur_id = ? AND ami_id = ?)
    ");
    $rel->execute([$userId, $u['id'], $u['id'], $userId]);
    $row = $rel->fetch();

    $results[] = [
        'id'            => $u['id'],
        'pseudo'        => $u['pseudo'],
        'nom_complet'   => $u['nom_complet'],
        'photo_profil'  => $u['photo_profil'],
        'relation'      => $row ? $row['statut'] : 'none' 
    ];
}

echo json_encode($results);
?>
