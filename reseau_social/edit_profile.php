<?php
$pageTitle = 'Modifier mon profil';
require_once 'includes/header.php';

$success = $_SESSION['profile_success'] ?? null;
unset($_SESSION['profile_success']);

///Image par défaut si pas de photo
$photoProfile = $currentUser['photo_profil'];
if (empty($photoProfile) || !file_exists($photoProfile)) {
    $photoProfile = 'uploads/profils/default.png';
}
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-custom">
            <div class="card-header fw-semibold">
                <i class="bi bi-gear me-2 text-primary"></i> Paramètres du profil
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="actions/edit_profile.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="<?= htmlspecialchars($photoProfile) ?>"
                                 alt="" class="avatar-xl" id="previewProfil">
                            <label for="photoInput" class="btn btn-sm btn-primary-custom position-absolute bottom-0 end-0 rounded-circle p-1"
                                   style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-camera"></i>
                                <input type="file" name="photo_profil" id="photoInput" class="d-none" accept="image/*">
                            </label>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="nom_complet" class="form-control"
                               value="<?= htmlspecialchars($currentUser['nom_complet'] ?? '') ?>" required>
                    </div>

                    
                    <div class="mb-4">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3"
                                  placeholder="Dites quelque chose sur vous..."><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-save me-2"></i> Sauvegarder
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('previewProfil').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>