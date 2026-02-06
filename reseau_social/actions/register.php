<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$pseudo          = trim($_POST['pseudo'] ?? '');
$nom_complet     = trim($_POST['nom_complet'] ?? '');
$email           = trim($_POST['email'] ?? '');
$mot_de_passe    = $_POST['mot_de_passe'] ?? '';
$mot_confirm     = $_POST['mot_de_passe_confirm'] ?? '';

// Validations
if (empty($pseudo) || empty($nom_complet) || empty($email) || empty($mot_de_passe)) {
    $_SESSION['register_error'] = 'Tous les champs sont obligatoires.';
    header('Location: ../register.php');
    exit;
}

if ($mot_de_passe !== $mot_confirm) {
    $_SESSION['register_error'] = 'Les mots de passe ne correspondent pas.';
    header('Location: ../register.php');
    exit;
}

if (strlen($mot_de_passe) < 6) {
    $_SESSION['register_error'] = 'Le mot de passe doit avoir au moins 6 caractères.';
    header('Location: ../register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Email invalide.';
    header('Location: ../register.php');
    exit;
}

//Vérifie unicité pseudo / email 
$check = $pdo->prepare("SELECT id FROM utilisateurs WHERE pseudo = ? OR email = ?");
$check->execute([$pseudo, $email]);
if ($check->fetch()) {
    $_SESSION['register_error'] = 'Ce pseudo ou cet email est déjà utilisé.';
    header('Location: ../register.php');
    exit;
}

//Insertion
$hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

$insert = $pdo->prepare(
    "INSERT INTO utilisateurs (pseudo, email, mot_de_passe, nom_complet, photo_profil)
     VALUES (?, ?, ?, ?, ?)"
);
$insert->execute([$pseudo, $email, $hash, $nom_complet, 'uploads/profils/default.png']);

header('Location: ../login.php');
exit;
?>
