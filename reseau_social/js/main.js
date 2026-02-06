/*RÉSEAU SOCIAL MINI */

/*----Like (toggle)===*/
function toggleLike(postId) {
    fetch('ajax/likes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'publication_id=' + postId
    })
    .then(r => r.json())
    .then(data => {
        const btn  = document.getElementById('like-btn-' + postId);
        const icon = document.getElementById('like-icon-' + postId);
        const cnt  = document.getElementById('like-count-' + postId);

        if (data.liked) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-danger');
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
        } else {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-outline-secondary');
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
        }
        cnt.textContent = data.count;
    });
}

/*envois Du demande*/
function sendFriendRequest(targetId) {
    fetch('ajax/friends.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=send&target_id=' + targetId
    })
    .then(r => r.json())
    .then(data => {
        const btn = document.getElementById('add-friend-' + targetId);
        if (btn && data.success) {
            btn.outerHTML = `<span class="btn btn-sm btn-outline-secondary disabled">
                <i class="fas fa-clock"></i> En attente
            </span>`;
        }
    });
}

/*acceptation or refus */
function respondFriend(sourceId, action, btn) {
    fetch('ajax/friends.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=' + action + '&target_id=' + sourceId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const row = btn.closest('.d-flex.align-items-center');
            if (row) {
                row.style.transition = 'opacity .3s, transform .3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => row.remove(), 320);
            }
        }
    });
}

/*supprission */
function removeFriend(targetId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet ami ?')) return;
    fetch('ajax/friends.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=remove&target_id=' + targetId
    })
    .then(r => r.json())
    .then(() => location.reload());
}

/*Supprimer la publiCation */
function deletePost(postId) {
    if (!confirm('Supprimer cette publication ?')) return;
    fetch('ajax/delete_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + postId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = document.getElementById('post-' + postId);
            if (card) {
                card.style.transition = 'opacity .3s, max-height .3s';
                card.style.opacity = '0';
                card.style.maxHeight = '0';
                card.style.overflow = 'hidden';
                setTimeout(() => card.remove(), 350);
            } else {
                location.reload();
            }
        }
    });
}

/*Supprission du notification*/
function deleteNotif(notifId) {
    fetch('ajax/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=delete&notif_id=' + notifId
    })
    .then(r => r.json())
    .then(() => {
        const el = document.getElementById('notif-' + notifId);
        if (el) el.remove();
    });
}

/*Effacer toutes les notifications*/
function clearNotifications() {
    if (!confirm('Effacer toutes les notifications ?')) return;
    fetch('ajax/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=clear'
    })
    .then(r => r.json())
    .then(() => {
        const list = document.getElementById('notifList');
        if (list) {
            list.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Aucune notification.</p>
                </div>`;
        }
    });
}

/*préview image dans le formulaire de post*/
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('imgPublication');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                document.getElementById('previewImg').src = ev.target.result;
                document.getElementById('previewContainer').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }
});

/*--------------------AJAX POLLING ------------------*/
(function startPolling() {
    setInterval(function() {
        fetch('ajax/notifications.php?action=count')
            .then(r => r.json())
            .then(data => {
                updateBadge('.nav-item:has(a[href="notifications.php"]) .badge-notif',
                            '.nav-item:has(a[href="notifications.php"]) .nav-link',
                            data.notifications);
                updateBadge('.nav-item:has(a[href="messages.php"]) .badge-notif',
                            '.nav-item:has(a[href="messages.php"]) .nav-link',
                            data.messages);
                updateBadge('.nav-item:has(a[href="amis.php"]) .badge-notif',
                            '.nav-item:has(a[href="amis.php"]) .nav-link',
                            data.friends);
            })
            .catch(() => {});  
    }, 30000); 
})();

/**
 * Met à jourun badge dans la navbar.
 * @param {string} badgeSelector  – sélecteur du badge existant
 * @param {string} linkSelector   – sélecteur du lien parent où insérer le badge
 * @param {number} count          – nombre à afficher
 */
function updateBadge(badgeSelector, linkSelector, count) {
    let badge = document.querySelector(badgeSelector);

    if (count > 0) {
        if (!badge) {
            const link = document.querySelector(linkSelector);
            if (!link) return;
            badge = document.createElement('span');
            badge.className = 'badge-notif';
            link.appendChild(badge);
        }
        badge.textContent = count;
    } else {
        if (badge) badge.remove();
    }
}
