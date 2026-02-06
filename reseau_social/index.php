<?php
$pageTitle = "Fil d'actualité";
require_once 'includes/header.php';

function getDefaultPhoto($photo) {
    if (empty($photo) || !file_exists($photo)) {
        return 'uploads/profils/default.png';
    }
    return $photo;
}

//Recuperation
$feedQuery = $pdo->prepare("
    SELECT p.*, u.pseudo, u.nom_complet, u.photo_profil,
           (SELECT COUNT(*) FROM likes WHERE publication_id = p.id) as nb_likes,
           (SELECT 1 FROM likes WHERE publication_id = p.id AND utilisateur_id = ?) as liked
    FROM publications p
    JOIN utilisateurs u ON p.utilisateur_id = u.id
    WHERE p.utilisateur_id = ?
       OR p.utilisateur_id IN (
            SELECT ami_id   FROM amis WHERE utilisateur_id = ? AND statut = 'accepte'
            UNION
            SELECT utilisateur_id FROM amis WHERE ami_id = ? AND statut = 'accepte'
         )
    ORDER BY p.date_publication DESC
    LIMIT 50
");
$feedQuery->execute([$userId, $userId, $userId, $userId]);
$posts = $feedQuery->fetchAll();
?>

<div class="row">
    
    <div class="col-lg-8">

        <!-------- La Formulaire de publication -->
        <div class="card card-custom mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <img src="<?= htmlspecialchars(getDefaultPhoto($currentUser['photo_profil'])) ?>" alt="" class="avatar-sm">
                    <div class="flex-grow-1">
                        <form action="actions/publish.php" method="POST" enctype="multipart/form-data" id="formPublication">
                            <textarea name="contenu" class="form-control textarea-post" rows="2"
                                      placeholder="Qu'est-ce qui se passe ?" required></textarea>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <label class="btn btn-sm btn-outline-secondary btn-upload" for="imgPublication">
                                    <i class="bi bi-image me-1"></i> Image
                                    <input type="file" name="image" id="imgPublication" class="d-none" accept="image/*">
                                </label>
                                <button type="submit" class="btn btn-primary-custom btn-sm">
                                    <i class="bi bi-send me-1"></i> Publier
                                </button>
                            </div>
                            <!---------preview image-------------------->
                            <div id="previewContainer" class="mt-2 d-none">
                                <img id="previewImg" src="" alt="" class="img-fluid rounded" style="max-height:180px;">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!----------FIL D'ACTUALITÉ-------------->
        <?php if (empty($posts)): ?>
            <div class="card card-custom text-center py-5">
                <i class="bi bi-newspaper fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune publication pour le moment.<br>
                   Ajoutez des amis ou publiez quelque chose !</p>
            </div>
        <?php endif; ?>

        <?php foreach ($posts as $post): ?>
            <div class="card card-custom mb-3" id="post-<?= $post['id'] ?>">
                <div class="card-body">
                    <!--Auteur + date-------->
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <a href="profile.php?id=<?= $post['utilisateur_id'] ?>">
                            <img src="<?= htmlspecialchars(getDefaultPhoto($post['photo_profil'])) ?>"
                                 alt="" class="avatar-sm">
                        </a>
                        <div class="flex-grow-1">
                            <a href="profile.php?id=<?= $post['utilisateur_id'] ?>" class="link-dark fw-semibold text-decoration-none">
                                <?= htmlspecialchars($post['nom_complet']) ?>
                            </a>
                            <div class="text-muted small">
                                <?= date('j M Y à H:i', strtotime($post['date_publication'])) ?>
                            </div>
                        </div>
                        <!-- Supprimer==-->
                        <?php if ($post['utilisateur_id'] === $userId): ?>
                            <button class="btn btn-sm btn-link text-muted p-0" onclick="deletePost(<?= $post['id'] ?>)" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!------=-Contenu -->
                    <p class="post-content"><?= htmlspecialchars($post['contenu']) ?></p>

                    <!-- Image------------->
                    <?php if ($post['image']): ?>
                        <img src="<?= htmlspecialchars($post['image']) ?>" alt="" class="img-fluid rounded post-image mb-2">
                    <?php endif; ?>

                    <!-- actionss------>
                    <div class="d-flex align-items-center gap-3 border-top pt-3 mt-2">
                        <button class="btn btn-sm <?= $post['liked'] ? 'btn-danger' : 'btn-outline-secondary' ?> d-flex align-items-center gap-1"
                                id="like-btn-<?= $post['id'] ?>"
                                onclick="toggleLike(<?= $post['id'] ?>)">
                            <i class="<?= $post['liked'] ? 'bi bi-heart-fill' : 'bi bi-heart' ?>" id="like-icon-<?= $post['id'] ?>"></i>
                            <span id="like-count-<?= $post['id'] ?>"><?= $post['nb_likes'] ?></span>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!------SIDEBAR--------------------->
    <div class="col-lg-4">
        <!--===profil -->
        <div class="card card-custom mb-3">
            <div class="card-body text-center">
                <img src="<?= htmlspecialchars(getDefaultPhoto($currentUser['photo_profil'])) ?>" alt="" class="avatar-lg mb-2">
                <h6 class="fw-semibold mb-0"><?= htmlspecialchars($currentUser['nom_complet']) ?></h6>
                <small class="text-muted">@<?= htmlspecialchars($currentUser['pseudo']) ?></small>
                <a href="profile.php?id=<?= $userId ?>" class="btn btn-sm btn-outline-secondary d-block mt-2">
                    Mon profil
                </a>
            </div>
        </div>

        <!-- Suggestions des amis ------->
        <div class="card card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-person-plus me-2 text-primary"></i> Suggestions
            </div>
            <div class="card-body">
                <?php
                $suggestions = $pdo->prepare("
                    SELECT u.id, u.pseudo, u.nom_complet, u.photo_profil
                    FROM utilisateurs u
                    WHERE u.id != ?
                      AND u.id NOT IN (
                            SELECT ami_id FROM amis WHERE utilisateur_id = ?
                            UNION
                            SELECT utilisateur_id FROM amis WHERE ami_id = ?
                          )
                    ORDER BY RAND()
                    LIMIT 5
                ");
                $suggestions->execute([$userId, $userId, $userId]);
                $sugList = $suggestions->fetchAll();
                ?>
                <?php if (empty($sugList)): ?>
                    <p class="text-muted small mb-0">Aucune suggestion pour le moment.</p>
                <?php endif; ?>
                <?php foreach ($sugList as $sug): ?>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <img src="<?= htmlspecialchars(getDefaultPhoto($sug['photo_profil'])) ?>" alt="" class="avatar-xs">
                        <div class="flex-grow-1">
                            <a href="profile.php?id=<?= $sug['id'] ?>" class="link-dark text-decoration-none small fw-semibold">
                                <?= htmlspecialchars($sug['nom_complet']) ?>
                            </a>
                            <div class="text-muted" style="font-size:.75rem">@<?= htmlspecialchars($sug['pseudo']) ?></div>
                        </div>
                        <button class="btn btn-sm btn-primary-custom" onclick="sendFriendRequest(<?= $sug['id'] ?>)" id="add-friend-<?= $sug['id'] ?>">
                            +
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>