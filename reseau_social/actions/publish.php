<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$userId = requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$contenu = trim($_POST['contenu'] ?? '');
if (empty($contenu)) {
    header('Location: ../index.php');
    exit;
}

$imagePath = null;

//uploder une image {5 mo max sizze}
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowedTypes)) {
        header('Location: ../index.php');
        exit;
    }
    if ($file['size'] > $maxSize) {
        header('Location: ../index.php');
        exit;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName  = uniqid('pub_', true) . '.' . $ext;
    $dest     = __DIR__ . '/../uploads/publications/' . $newName;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        $imagePath = 'uploads/publications/' . $newName;
    }
}

//Insertion
$stmt = $pdo->prepare(
    "INSERT INTO publications (utilisateur_id, contenu, image) VALUES (?, ?, ?)"
);
$stmt->execute([$userId, $contenu, $imagePath]);

header('Location: ../index.php');
exit;
?>
