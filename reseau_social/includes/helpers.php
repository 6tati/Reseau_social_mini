<?php
/**
 * Retourne le chemin de la photo de profil ou l'image par défaut
 */
function getProfilePhoto($photoPath) {
    if (empty($photoPath) || !file_exists($photoPath)) {
        return 'uploads/profils/default.png';
    }
    return $photoPath;
}