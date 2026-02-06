<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$userId = requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../edit_profile.php');
    exit;
}

$nom_complet = trim($_POST['nom_complet'] ?? '');
$bio         = trim($_POST['bio'] ?? '');
$photoPath   = null;

//uploader une nouvelle prfil Pic
if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === UPLOAD_ERR_OK && $_FILES['photo_profil']['size'] > 0) {
    $file = $_FILES['photo_profil'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 3 * 1024 * 1024; // 3 Mo

    if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
        $ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('profil_', true) . '.' . $ext;
        $dest    = __DIR__ . '/../uploads/profils/' . $newName;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $photoPath = 'uploads/profils/' . $newName;
        }
    }
}

//mettre a jour
if ($photoPath) {
    $pdo->prepare("UPDATE utilisateurs SET nom_complet = ?, bio = ?, photo_profil = ? WHERE id = ?")
        ->execute([$nom_complet, $bio, $photoPath, $userId]);
} else {
    $pdo->prepare("UPDATE utilisateurs SET nom_complet = ?, bio = ? WHERE id = ?")
        ->execute([$nom_complet, $bio, $userId]);
}

$_SESSION['profile_success'] = 'Profil mis à jour avec succès !';
header('Location: ../edit_profile.php');
exit;
?>
