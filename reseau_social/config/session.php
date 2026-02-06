<?php
// ── config/session.php ──────────────────────
session_start();

/**
 * Vérifie si l'utilisateur est connecté.
 * Si $redirect = true, redirige vers login sinon.
 */
function requireLogin(bool $redirect = true): ?int {
    if (!isset($_SESSION['user_id'])) {
        if ($redirect) {
            header('Location: login.php');
            exit;
        }
        return null;
    }
    return $_SESSION['user_id'];
}
?>
