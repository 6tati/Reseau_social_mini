<?php
$pageTitle = 'Notifications';
require_once 'includes/header.php';

// Marque toutes les notifs comme lues
$pdo->prepare("UPDATE notifications SET lu = 1 WHERE utilisateur_id = ?")
    ->execute([$userId]);

// Récupère les notifications
$notifs = $pdo->prepare("
    SELECT n.*, u.pseudo, u.nom_complet, u.photo_profil
    FROM notifications n
    JOIN utilisateurs u ON u.id = n.source_id
    WHERE n.utilisateur_id = ?
    ORDER BY n.date_notif DESC
    LIMIT 50
");
$notifs->execute([$userId]);
$notifications = $notifs->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell me-2 text-primary"></i> Notifications</span>
                <?php if (!empty($notifications)): ?>
                    <button class="btn btn-sm btn-outline-secondary" onclick="clearNotifications()">
                        <i class="bi bi-trash me-1"></i> Tout effacer
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0" id="notifList">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Aucune notification.</p>
                    </div>
                <?php endif; ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="d-flex align-items-start gap-3 p-3 border-bottom notif-item" id="notif-<?= $notif['id'] ?>">
                        <img src="<?= htmlspecialchars($notif['photo_profil'] ?? 'uploads/profils/default.png') ?>"
                             alt="" class="avatar-sm">
                        <div class="flex-grow-1">
                            <div class="small">
                                <strong><?= htmlspecialchars($notif['nom_complet']) ?></strong>
                                <?php 
                                switch ($notif['type']) {
                                    case 'ami':
                                        echo 'vous a envoyé une <span class="text-primary">demande d\'ami</span> ou a accepté votre demande.';
                                        break;
                                    case 'like':
                                        echo 'a <span class="text-danger">aimé</span> une de vos publications.';
                                        break;
                                    case 'message':
                                        echo 'vous a envoyé un <span class="text-success">message</span>.';
                                        break;
                                }
                                ?>
                            </div>
                            <div class="text-muted mt-1" style="font-size:.72rem">
                                <?= date('j M Y à H:i', strtotime($notif['date_notif'])) ?>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-column gap-1">
                            <?php if ($notif['type'] === 'ami'): ?>
                                <a href="amis.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-people-fill me-1"></i> Voir
                                </a>
                            <?php elseif ($notif['type'] === 'message'): ?>
                                <a href="messages.php?user=<?= $notif['source_id'] ?>" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-reply me-1"></i> Répondre
                                </a>
                            <?php elseif ($notif['type'] === 'like'): ?>
                                <a href="profile.php?id=<?= $notif['source_id'] ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-person me-1"></i> Voir
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-secondary" onclick="deleteNotif(<?= $notif['id'] ?>)">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
