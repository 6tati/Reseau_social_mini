
--RÉSEAU SOCIAL MINI — Base de données


CREATE DATABASE IF NOT EXISTS reseau_social;
USE reseau_social;

--Utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    pseudo          VARCHAR(50)  UNIQUE NOT NULL,
    email           VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe    VARCHAR(255) NOT NULL,
    nom_complet     VARCHAR(200),
    bio             TEXT,
    photo_profil    VARCHAR(255) DEFAULT 'uploads/profils/default.png',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Publications
CREATE TABLE IF NOT EXISTS publications (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id   INT NOT NULL,
    contenu          TEXT NOT NULL,
    image            VARCHAR(255),
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

--Amiss
CREATE TABLE IF NOT EXISTS amis (
    utilisateur_id INT NOT NULL,
    ami_id         INT NOT NULL,
    statut         ENUM('en_attente','accepte') DEFAULT 'en_attente',
    date_demande   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id, ami_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (ami_id)         REFERENCES utilisateurs(id) ON DELETE CASCADE
);

--Messages privés
CREATE TABLE IF NOT EXISTS messages_prives (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    expediteur_id   INT NOT NULL,
    destinataire_id INT NOT NULL,
    message         TEXT NOT NULL,
    lu              BOOLEAN DEFAULT FALSE,
    date_envoi      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id)   REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── Likes ───────────────────────────────────
CREATE TABLE IF NOT EXISTS likes (
    publication_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_like      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (publication_id, utilisateur_id),
    FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ── Notifications ───────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    type          ENUM('ami','like','message') NOT NULL,
    source_id     INT NOT NULL,
    lu            BOOLEAN DEFAULT FALSE,
    date_notif    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (source_id)      REFERENCES utilisateurs(id) ON DELETE CASCADE
);
