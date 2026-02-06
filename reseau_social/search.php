<?php
$pageTitle = 'Recherche';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-search me-2 text-primary"></i> Rechercher des utilisateurs
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control"
                           placeholder="Tapez un pseudo ou un nom..." autocomplete="off">
                </div>

                
                <div id="searchResults">
                    <div class="text-center py-4">
                        <i class="bi bi-people-fill fa-2x text-muted mb-2"></i>
                        <p class="text-muted">Recherchez un utilisateur ci-dessus.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let searchTimeout;

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    const resultsDiv = document.getElementById('searchResults');

    if (query.length < 2) {
        resultsDiv.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-people-fill fa-2x text-muted mb-2"></i>
                <p class="text-muted">Tapez au moins 2 caractères.</p>
            </div>`;
        return;
    }

    resultsDiv.innerHTML = '<div class="text-center py-3"><i class="spinner-border spinner-border-sm text-muted"></i></div>';

    searchTimeout = setTimeout(() => {
        fetch('ajax/search.php?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(users => {
                if (users.length === 0) {
                    resultsDiv.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-search fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Aucun utilisateur trouvé.</p>
                        </div>`;
                    return;
                }
                let html = '';
                users.forEach(u => {
                    html += `
                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                        <a href="profile.php?id=${u.id}">
                            <img src="${u.photo_profil || 'uploads/profils/default.png'}" alt="" class="avatar-sm">
                        </a>
                        <div class="flex-grow-1">
                            <a href="profile.php?id=${u.id}" class="link-dark fw-semibold text-decoration-none small">
                                ${u.nom_complet}
                            </a>
                            <div class="text-muted" style="font-size:.72rem">@${u.pseudo}</div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="profile.php?id=${u.id}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            ${u.relation === 'none' ? `
                                <button class="btn btn-sm btn-primary-custom" onclick="sendFriendRequest(${u.id})" id="add-friend-${u.id}">
                                    <i class="bi bi-person-plus"></i> +
                                </button>` : `
                                <span class="btn btn-sm btn-outline-success disabled">
                                    <i class="fas fa-${u.relation === 'accepte' ? 'check' : 'clock'}"></i>
                                </span>`
                            }
                        </div>
                    </div>`;
                });
                resultsDiv.innerHTML = html;
            })
            .catch(() => {
                resultsDiv.innerHTML = '<p class="text-danger text-center">Erreur lors de la recherche.</p>';
            });
    }, 400);
});
</script>

<?php require_once 'includes/footer.php'; ?>
