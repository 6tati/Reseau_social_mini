<?php
// ── amis.php ────────────────────────────────
$pageTitle = 'Mes amis';
require_once 'includes/header.php';

// ── Demandes en attente (reçues) ────────────
$pending = $pdo->prepare("
    SELECT a.utilisateur_id, u.pseudo, u.nom_complet, u.photo_profil, a.date_demande
    FROM amis a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE a.ami_id = ? AND a.statut = 'en_attente'
    ORDER BY a.date_demande DESC
");
$pending->execute([$userId]);
$pendingList = $pending->fetchAll();

// ── Liste des amis acceptés ────────────────
$friends = $pdo->prepare("
    SELECT
        CASE WHEN a.utilisateur_id = ? THEN a.ami_id ELSE a.utilisateur_id END as ami_id,
        u.pseudo, u.nom_complet, u.photo_profil
    FROM amis a
    JOIN utilisateurs u ON u.id = CASE WHEN a.utilisateur_id = ? THEN a.ami_id ELSE a.utilisateur_id END
    WHERE (a.utilisateur_id = ? OR a.ami_id = ?) AND a.statut = 'accepte'
    ORDER BY u.nom_complet ASC
");
$friends->execute([$userId, $userId, $userId, $userId]);
$friendList = $friends->fetchAll();
?>

<div class="row">
    <div class="col-lg-8">

        <!-- ── DEMANDES EN ATTENTE ──────────────── -->
        <?php if (!empty($pendingList)): ?>
            <div class="card card-custom mb-4">
                <div class="card-header fw-semibold">
                    <i class="bi bi-person-clock me-2 text-warning"></i>
                    Demandes d'amis
                    <span class="badge bg-warning text-dark ms-2"><?= count($pendingList) ?></span>
                </div>
                <div class="card-body">
                    <?php foreach ($pendingList as $req): ?>
                        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                            <a href="profile.php?id=<?= $req['utilisateur_id'] ?>">
                                <img src="<?= htmlspecialchars($req['photo_profil'] ?? 'uploads/profils/default.png') ?>"
                                     alt="" class="avatar-sm">
                            </a>
                            <div class="flex-grow-1">
                                <a href="profile.php?id=<?= $req['utilisateur_id'] ?>" class="link-dark fw-semibold text-decoration-none">
                                    <?= htmlspecialchars($req['nom_complet']) ?>
                                </a>
                                <div class="text-muted small">
                                    @<?= htmlspecialchars($req['pseudo']) ?> · <?= date('j M Y', strtotime($req['date_demande'])) ?>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-success"
                                        onclick="respondFriend(<?= $req['utilisateur_id'] ?>, 'accepte', this)">
                                    <i class="bi bi-check"></i> Accepter
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="respondFriend(<?= $req['utilisateur_id'] ?>, 'refuse', this)">
                                    <i class="bi bi-x"></i> Refuser
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── LISTE DES AMIS ───────────────────── -->
        <div class="card card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-people-fill me-2 text-primary"></i>
                Amis
                <span class="badge bg-primary ms-2"><?= count($friendList) ?></span>
            </div>
            <div class="card-body">
                <?php if (empty($friendList)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Vous n'avez pas encore d'amis.<br>
                           Utilisez la recherche pour en trouver !</p>
                    </div>
                <?php endif; ?>
                <?php foreach ($friendList as $friend): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <a href="profile.php?id=<?= $friend['ami_id'] ?>">
                            <img src="<?= htmlspecialchars($friend['photo_profil'] ?? 'uploads/profils/default.png') ?>"
                                 alt="" class="avatar-sm">
                        </a>
                        <div class="flex-grow-1">
                            <a href="profile.php?id=<?= $friend['ami_id'] ?>" class="link-dark fw-semibold text-decoration-none">
                                <?= htmlspecialchars($friend['nom_complet']) ?>
                            </a>
                            <div class="text-muted small">@<?= htmlspecialchars($friend['pseudo']) ?></div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="messages.php?user=<?= $friend['ami_id'] ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-envelope"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeFriend(<?= $friend['ami_id'] ?>)">
                                <i class="bi bi-person-times"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-search me-2 text-primary"></i> Trouver des amis
            </div>
            <div class="card-body">
                <!-- Barre de recherche inline -->
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="quickSearchInput" class="form-control form-control-sm"
                           placeholder="Rechercher..." autocomplete="off">
                </div>

                <!-- Résultats -->
                <div id="quickSearchResults">
                    <p class="text-muted small mb-0">Tapez un nom ou un pseudo pour rechercher.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let quickSearchTimeout;

document.getElementById('quickSearchInput').addEventListener('input', function() {
    clearTimeout(quickSearchTimeout);
    const query = this.value.trim();
    const resultsDiv = document.getElementById('quickSearchResults');

    if (query.length < 2) {
        resultsDiv.innerHTML = '<p class="text-muted small mb-0">Tapez au moins 2 caractères.</p>';
        return;
    }

    resultsDiv.innerHTML = '<div class="text-center py-2"><i class="spinner-border spinner-border-sm text-muted"></i></div>';

    quickSearchTimeout = setTimeout(() => {
        fetch('ajax/search.php?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(users => {
                if (users.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-muted small mb-0">Aucun résultat.</p>';
                    return;
                }
                let html = '';
                users.forEach(u => {
                    html += `
                    <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                        <img src="${u.photo_profil || 'uploads/profils/default.png'}" alt="" class="avatar-xs">
                        <div class="flex-grow-1" style="min-width:0;">
                            <a href="profile.php?id=${u.id}" class="link-dark text-decoration-none small fw-semibold d-block text-truncate">
                                ${u.nom_complet}
                            </a>
                            <div class="text-muted" style="font-size:.7rem">@${u.pseudo}</div>
                        </div>
                        ${u.relation === 'none' ? `
                            <button class="btn btn-sm btn-primary-custom" onclick="sendFriendRequest(${u.id})" id="add-friend-${u.id}">
                                <i class="bi bi-person-plus"></i>
                            </button>` : `
                            <span class="badge bg-${u.relation === 'accepte' ? 'success' : 'secondary'}">
                                <i class="fas fa-${u.relation === 'accepte' ? 'check' : 'clock'}"></i>
                            </span>`
                        }
                    </div>`;
                });
                resultsDiv.innerHTML = html;
            })
            .catch(() => {
                resultsDiv.innerHTML = '<p class="text-danger small">Erreur de recherche.</p>';
            });
    }, 400);
});
</script>

<?php require_once 'includes/footer.php'; ?>
