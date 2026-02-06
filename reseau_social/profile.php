<?php
require_once 'includes/header.php';

$profileId = (int)($_GET['id'] ?? 0);

if ($profileId === 0) {
    header('Location: index.php');
    exit;
}

// Récupère l'utilisateur du profil
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$profileId]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header('Location: index.php');
    exit;
}

$pageTitle = "Profil de " . htmlspecialchars($profileUser['nom_complet']);

// sstats
$nbPosts = $pdo->prepare("SELECT COUNT(*) as cnt FROM publications WHERE utilisateur_id = ?");
$nbPosts->execute([$profileId]);
$postCount = $nbPosts->fetch()['cnt'];

$nbAmis = $pdo->prepare("
    SELECT COUNT(*) as cnt FROM amis
    WHERE (utilisateur_id = ? OR ami_id = ?) AND statut = 'accepte'
");
$nbAmis->execute([$profileId, $profileId]);
$friendCount = $nbAmis->fetch()['cnt'];

$amiStatut = null; // null = pas ami
if ($profileId !== $userId) {
    $checkAmi = $pdo->prepare("
        SELECT statut FROM amis
        WHERE (utilisateur_id = ? AND ami_id = ?)
           OR (utilisateur_id = ? AND ami_id = ?)
    ");
    $checkAmi->execute([$userId, $profileId, $profileId, $userId]);
    $row = $checkAmi->fetch();
    $amiStatut = $row ? $row['statut'] : null;
}

//Publications du profil 
$posts = $pdo->prepare("
    SELECT p.*, u.pseudo, u.nom_complet, u.photo_profil,
           (SELECT COUNT(*) FROM likes WHERE publication_id = p.id) as nb_likes,
           (SELECT 1 FROM likes WHERE publication_id = p.id AND utilisateur_id = :uid) as liked
    FROM publications p
    JOIN utilisateurs u ON p.utilisateur_id = u.id
    WHERE p.utilisateur_id = :pid
    ORDER BY p.date_publication DESC
    LIMIT 30
");
$posts->execute(['uid' => $userId, 'pid' => $profileId]);
$profilePosts = $posts->fetchAll();
?>

<!--  EN-TETE PROFIL -->
<div class="card card-custom mb-4 profile-header">
    <div class="card-body text-center py-4">
        <img src="<?= htmlspecialchars($profileUser['photo_profil'] ?? 'uploads/profils/default.png') ?>"
             alt="" class="avatar-xl mb-3">
        <h4 class="fw-bold mb-0"><?= htmlspecialchars($profileUser['nom_complet']) ?></h4>
        <p class="text-muted mb-2">@<?= htmlspecialchars($profileUser['pseudo']) ?></p>
        <?php if (!empty($profileUser['bio'])): ?>
            <p class="profile-bio"><?= htmlspecialchars($profileUser['bio']) ?></p>
        <?php endif; ?>

        <!-- stats -->
        <div class="d-flex justify-content-center gap-4 my-3">
            <div class="text-center">
                <div class="fw-bold"><?= $postCount ?></div>
                <small class="text-muted">Publications</small>
            </div>
            <div class="text-center">
                <div class="fw-bold"><?= $friendCount ?></div>
                <small class="text-muted">Amis</small>
            </div>
        </div>

        <!-- actions -->
        <?php if ($profileId === $userId): ?>
            <a href="edit_profile.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil me-1"></i> Modifier le profil
            </a>
        <?php else: ?>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <?php if ($amiStatut === null): ?>
                    <button class="btn btn-primary-custom btn-sm" onclick="sendFriendRequest(<?= $profileId ?>)" id="add-friend-<?= $profileId ?>">
                        <i class="bi bi-person-plus me-1"></i> Ajouter ami
                    </button>
                <?php elseif ($amiStatut === 'en_attente'): ?>
                    <span class="btn btn-sm btn-outline-secondary disabled">
                        <i class="bi bi-clock me-1"></i> En attente
                    </span>
                <?php elseif ($amiStatut === 'accepte'): ?>
                    <span class="btn btn-sm btn-outline-success disabled">
                        <i class="bi bi-check me-1"></i> Ami
                    </span>
                <?php endif; ?>
                <a href="messages.php?user=<?= $profileId ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-envelope me-1"></i> Message
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- lesPUBLICATIONS -->
<h5 class="fw-semibold mb-3">Publications</h5>
<?php if (empty($profilePosts)): ?>
    <div class="card card-custom text-center py-4">
        <i class="bi bi-file-text fa-2x text-muted mb-2"></i>
        <p class="text-muted mb-0">Aucune publication encore.</p>
    </div>
<?php endif; ?>

<?php foreach ($profilePosts as $post): ?>
    <div class="card card-custom mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="<?= htmlspecialchars($post['photo_profil'] ?? 'uploads/profils/default.png') ?>" alt="" class="avatar-xs">
                <div>
                    <span class="fw-semibold small"><?= htmlspecialchars($post['nom_complet']) ?></span>
                    <div class="text-muted" style="font-size:.72rem">
                        <?= date('j M Y à H:i', strtotime($post['date_publication'])) ?>
                    </div>
                </div>
                <?php if ($post['utilisateur_id'] === $userId): ?>
                    <button class="btn btn-sm btn-link text-muted p-0 ms-auto" onclick="deletePost(<?= $post['id'] ?>)">
                        <i class="bi bi-trash"></i>
                    </button>
                <?php endif; ?>
            </div>
            <p class="post-content"><?= htmlspecialchars($post['contenu']) ?></p>
            <?php if ($post['image']): ?>
                <img src="<?= htmlspecialchars($post['image']) ?>" alt="" class="img-fluid rounded post-image mb-2">
            <?php endif; ?>
            <div class="border-top pt-2 mt-2">
                <button class="btn btn-sm btn-link p-0 text-decoration-none <?= $post['liked'] ? 'text-danger' : 'text-muted' ?>"
                        id="like-btn-<?= $post['id'] ?>" onclick="toggleLike(<?= $post['id'] ?>)">
                    <i class="<?= $post['liked'] ? 'fas' : 'far' ?> fa-heart me-1" id="like-icon-<?= $post['id'] ?>"></i>
                    <span id="like-count-<?= $post['id'] ?>"><?= $post['nb_likes'] ?></span>
                </button>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>
