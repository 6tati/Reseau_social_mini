<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$identifiant = trim($_POST['identifiant'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

if (empty($identifiant) || empty($mot_de_passe)) {
    $_SESSION['login_error'] = 'Tous les champs sont obligatoires.';
    header('Location: ../login.php');
    exit;
}

//login par email or username'pseudo'
$stmt = $pdo->prepare(
    "SELECT * FROM utilisateurs WHERE email = ? OR pseudo = ? LIMIT 1"
);
$stmt->execute([$identifiant, $identifiant]);
$user = $stmt->fetch();

if (!$user || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
    $_SESSION['login_error'] = 'Identifiant ou mot de passe incorrect.';
    header('Location: ../login.php');
    exit;
}

//Connexion réussie
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_pseudo'] = $user['pseudo'];

header('Location: ../index.php');
exit;
?>
