<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$userId = requireLogin();

// Recuperation des donnee
$user = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$user->execute([$userId]);
$currentUser = $user->fetch();

// Notification
$notifCount = $pdo->prepare(
    "SELECT COUNT(*) as cnt FROM notifications WHERE utilisateur_id = ? AND lu = 0"
);
$notifCount->execute([$userId]);
$nCount = $notifCount->fetch()['cnt'];

// amis en attente
$friendReqCount = $pdo->prepare(
    "SELECT COUNT(*) as cnt FROM amis WHERE ami_id = ? AND statut = 'en_attente'"
);
$friendReqCount->execute([$userId]);
$fCount = $friendReqCount->fetch()['cnt'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Réseau Social' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!----------------NAVbaR-------------------->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php">
            <i class="bi bi-people-fill"></i> Réseau Social Mini
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <!-----search------ -->
                <li class="nav-item">
                    <a href="search.php" class="btn btn-light position-relative" title="Rechercher">
                        <i class="bi bi-search"></i>
                    </a>
                </li>
                <!---les Messages----------------->
                <li class="nav-item">
                    <a href="messages.php" class="btn btn-light position-relative" title="Messages">
                        <i class="bi bi-chat-dots"></i>
                        <?php
                        $msgCount = $pdo->prepare(
                            "SELECT COUNT(*) as cnt FROM messages_prives WHERE destinataire_id = ? AND lu = 0"
                        );
                        $msgCount->execute([$userId]);
                        $mCount = $msgCount->fetch()['cnt'];
                        if ($mCount > 0):
                        ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $mCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <!----les amis-------------->
                <li class="nav-item">
                    <a href="amis.php" class="btn btn-light position-relative" title="Amis">
                        <i class="bi bi-people"></i>
                        <?php if ($fCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $fCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <!-----NNotifications----------- -->
                <li class="nav-item">
                    <a href="notifications.php" class="btn btn-light position-relative" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <?php if ($nCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $nCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <li class="nav-item ms-2">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                            <!-- ✅ Plus besoin de ?? ici, déjà géré en haut -->
                            <img src="<?= htmlspecialchars($currentUser['photo_profil']) ?>"
                                 alt="Avatar" class="rounded-circle border border-2 border-primary" style="width:36px;height:36px;object-fit:cover;">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="profile.php?id=<?= $userId ?>">
                                <i class="bi bi-person me-2"></i> Mon profil
                            </a></li>
                            <li><a class="dropdown-item" href="edit_profile.php">
                                <i class="bi bi-gear me-2"></i> Paramètres
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="actions/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Déconnexion
                            </a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!----Contenue----------------------->
<div class="container main-container">