<?php
$pageTitle = 'Messagerie';
require_once 'includes/header.php';

$targetId = (int)($_GET['user'] ?? 0);

//------------Liste des conversations------
$convos = $pdo->prepare("
    SELECT
        CASE WHEN expediteur_id = ? THEN destinataire_id ELSE expediteur_id END as other_id,
        u.pseudo, u.nom_complet, u.photo_profil,
        MAX(m.date_envoi) as derniere_date,
        (SELECT message FROM messages_prives m2 WHERE m2.id = MAX(m.id)) as dernier_msg,
        SUM(CASE WHEN m.destinataire_id = ? AND m.lu = 0 THEN 1 ELSE 0 END) as non_lus
    FROM messages_prives m
    JOIN utilisateurs u ON u.id = CASE WHEN m.expediteur_id = ? THEN m.destinataire_id ELSE m.expediteur_id END
    WHERE m.expediteur_id = ? OR m.destinataire_id = ?
    GROUP BY other_id, u.pseudo, u.nom_complet, u.photo_profil
    ORDER BY derniere_date DESC
");
$convos->execute([$userId, $userId, $userId, $userId, $userId]);
$conversations = $convos->fetchAll();

$chatMessages = [];
$otherUser    = null;

if ($targetId > 0) {
    // Info de l'autre utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
    $stmt->execute([$targetId]);
    $otherUser = $stmt->fetch();

    //Marque comme lus
    $pdo->prepare("UPDATE messages_prives SET lu = 1 WHERE destinataire_id = ? AND expediteur_id = ?")
        ->execute([$userId, $targetId]);

    // Récupère les messages
    $msgs = $pdo->prepare("
        SELECT m.*, u.pseudo, u.nom_complet, u.photo_profil
        FROM messages_prives m
        JOIN utilisateurs u ON u.id = m.expediteur_id
        WHERE (m.expediteur_id = ? AND m.destinataire_id = ?)
           OR (m.expediteur_id = ? AND m.destinataire_id = ?)
        ORDER BY m.date_envoi ASC
    ");
    $msgs->execute([$userId, $targetId, $targetId, $userId]);
    $chatMessages = $msgs->fetchAll();
}
?>

<div class="row">
    <!---------------SIDEBAR CONVERSATIONS -------->
    <div class="col-md-4">
        <div class="card card-custom h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-envelope me-2 text-primary"></i> Conversations
            </div>
            <div class="card-body p-0" style="max-height:520px;overflow-y:auto;">
                <?php if (empty($conversations)): ?>
                    <div class="text-center p-4">
                        <i class="bi bi-chat-dots fa-2x text-muted mb-2"></i>
                        <p class="text-muted small">Aucune conversation pour le moment.</p>
                    </div>
                <?php endif; ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="messages.php?user=<?= $conv['other_id'] ?>"
                       class="d-flex align-items-center gap-3 p-3 text-decoration-none border-bottom
                              <?= ($targetId === $conv['other_id']) ? 'bg-light' : 'text-dark' ?>
                              hover-bg">
                        <img src="<?= htmlspecialchars($conv['photo_profil'] ?? 'uploads/profils/default.png') ?>"
                             alt="" class="avatar-sm">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold small text-truncate">
                                <?= htmlspecialchars($conv['nom_complet']) ?>
                            </div>
                            <div class="text-muted small text-truncate" style="font-size:.72rem">
                                <?= htmlspecialchars(substr($conv['dernier_msg'] ?? '', 0, 40)) ?>
                            </div>
                        </div>
                        <?php if ($conv['non_lus'] > 0): ?>
                            <span class="badge bg-primary rounded-pill"><?= $conv['non_lus'] ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!--------CHAT----->
    <div class="col-md-8">
        <div class="card card-custom h-100 d-flex flex-column">
            <?php if ($otherUser): ?>
                <!--header chat -->
                <div class="card-header d-flex align-items-center gap-3 fw-semibold">
                    <img src="<?= htmlspecialchars($otherUser['photo_profil'] ?? 'uploads/profils/default.png') ?>"
                         alt="" class="avatar-xs">
                    <a href="profile.php?id=<?= $targetId ?>" class="link-dark text-decoration-none">
                        <?= htmlspecialchars($otherUser['nom_complet']) ?>
                    </a>
                </div>

                <!-- mmessages -->
                <div class="card-body overflow-auto flex-grow-1" style="max-height:420px;" id="chatBox">
                    <?php foreach ($chatMessages as $msg): ?>
                        <?php $isOwn = ($msg['expediteur_id'] === $userId); ?>
                        <div class="d-flex <?= $isOwn ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
                            <div class="chat-bubble <?= $isOwn ? 'own' : 'other' ?>">
                                <div class="msg-text"><?= htmlspecialchars($msg['message']) ?></div>
                                <div class="msg-time">
                                    <?= date('H:i', strtotime($msg['date_envoi'])) ?>
                                    <?php if ($isOwn && $msg['lu']): ?>
                                        <i class="bi bi-check-double ms-1" style="font-size:.65rem"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!--formulaire d'envoi------->
                <div class="card-footer">
                    <form action="ajax/send_message.php" method="POST" class="d-flex gap-2" id="formMessage">
                        <input type="hidden" name="target_id" value="<?= $targetId ?>">
                        <input type="text" name="message" class="form-control" placeholder="Tapez un message..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary-custom">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <!------aucune convo selectionnee------------------>
                <div class="card-body d-flex align-items-center justify-content-center flex-grow-1">
                    <div class="text-center">
                        <i class="bi bi-chat-dots fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Sélectionnez une conversation ou choisissez un utilisateur pour messaging.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
//Auto-scroll vers le bas au chargement
document.addEventListener('DOMContentLoaded', function() {
    const box = document.getElementById('chatBox');
    if (box) box.scrollTop = box.scrollHeight;
});
</script>

<?php require_once 'includes/footer.php'; ?>
