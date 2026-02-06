
# 🌐 Réseau Social Mini

Une application web de réseau social développée en **PHP** et **MySQL**, permettant aux utilisateurs de se connecter, partager des publications, gérer des amis et s'échanger des messages privés.



## ✨ Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| 🔐 Authentification | Inscription, connexion et déconnexion sécurisées avec hachage BCRYPT |
| 📰 Fil d'actualité | Affiche les publications de l'utilisateur et de ses amis |
| 👥 Système d'amis | Envoyer, accepter, refuser ou supprimer des amis |
| 💬 Messagerie privée | Chat entre deux utilisateurs avec indicateur de lecture |
| ❤️ Likes | Like/unlike sur les publications en temps réel |
| 🔍 Recherche | Recherche d'utilisateurs en temps réel avec debounce |
| 🔔 Notifications | Alertes automatiques pour les amis, likes et messages |
| 📷 Upload de photos | Photos de profil et images dans les publications |
| ⚡ Polling AJAX | Rafraîchissement automatique des badges toutes les 30 secondes |

---

## 🛠 Technologies utilisées

- **Backend** : PHP 8.x
- **Base de données** : MySQL / MariaDB
- **ORM** : PDO (PHP Data Objects)
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Frameworks CSS** : Bootstrap 5.3
- **Icônes** : Font Awesome 6
- **Polices** : Google Fonts (DM Sans, Playfair Display)

---

## 📦 Prérequis

Assurez-vous d'avoir installé :

- **XAMPP** avec Apache et MySQL
- **PHP 8.0** ou supérieur
- Un navigateur web moderne (Chrome, Firefox, Edge)

---

## ⚙️ Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/6tati/Reseau_social_mini.git
```

### 2. Déplacer le dossier

Copier le dossier `reseau-social-mini` dans votre répertoire de serveur local :

- **XAMPP** → `C:/xampp/htdocs/reseau-social-mini/`

### 3. Démarrer les services

Lancez **Apache** et **MySQL** depuis le panneau de contrôle XAMPP/WAMP.

### 4. Importer la base de données

- Ouvrir **phpMyAdmin** → `http://localhost/phpmyadmin`
- Créer une nouvelle base de données nommée `reseau_social`
- Importer le fichier `database.sql`


### 5. Configurer la connexion

Ouvrir `config/database.php` et adapter les paramètres si nécessaire :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'reseau_social');
define('DB_USER', 'root');       // Votre utilisateur MySQL
define('DB_PASS', '');           // Votre mot de passe MySQL
```

### 6. Lancer l'application

Ouvrir votre navigateur et aller à :

```
http://localhost/reseau-social-mini/
```

---

## 📂 Structure du projet

```
reseau-social-mini/
│
├── config/
│   ├── database.php            # Connexion PDO à MySQL
│   └── session.php             # Gestion de la session PHP
│
├── includes/
│   ├── header.php              # Navbar + en-tête HTML partagé
│   └── footer.php              # Pied de page + scripts JS
│
├── actions/                    # Traitement des formulaires POST
│   ├── login.php               # Authentification
│   ├── logout.php              # Déconnexion
│   ├── register.php            # Inscription
│   ├── publish.php             # Créer une publication
│   └── edit_profile.php        # Modifier le profil
│
├── ajax/                       # Endpoints JSON pour les requêtes AJAX
│   ├── likes.php               # Toggle like
│   ├── friends.php             # Ami : send / accept / refuse / remove
│   ├── send_message.php        # Envoyer un message
│   ├── search.php              # Recherche d'utilisateurs
│   ├── notifications.php       # Polling + gestion des notifications
│   └── delete_post.php         # Supprimer une publication
│
├── uploads/
│   ├── profils/                # Photos de profil
│   │   └── default.png         # Avatar par défaut
│   └── publications/           # Images des publications
│
├── css/
│   └── style.css               # Styles personnalisés (variables CSS, navbar, chat, etc.)
│
├── js/
│   └── main.js                 # AJAX : likes, amis, messages, polling
│
├── index.php                   # Fil d'actualité (page principale)
├── login.php                   # Page de connexion
├── register.php                # Page d'inscription
├── profile.php                 # Profil d'un utilisateur
├── edit_profile.php            # Modification du profil
├── amis.php                    # Liste des amis et demandes
├── messages.php                # Messagerie privée
├── search.php                  # Recherche d'utilisateurs
├── notifications.php           # Centre de notifications
└── database.sql                # Script SQL pour créer la base de données
```

---

## 🔧 Configuration

### Permissions des dossiers uploads

Assurez-vous que les dossiers suivants sont **en écriture** pour le serveur web :

```
uploads/profils/
uploads/publications/
```



### Limites d'upload PHP

Si vous rencontrez des problèmes d'upload, vérifiez dans votre `php.ini` :

```ini
upload_max_filesize = 10M
post_max_size = 10M
```

---

## 🚀 Utilisation

1. **Créer un compte** → Cliquez sur « Créer un compte » depuis la page de login
2. **Se connecter** → Utilisez votre email ou pseudo + mot de passe
3. **Publier** → Tapez un message (avec ou sans image) dans le fil d'actualité
4. **Ajouter des amis** → Utilisez la recherche ou les suggestions pour envoyer des demandes
5. **Envoyer un message** → Cliquez sur l'icône ✉️ ou allez dans « Messages »
6. **Like** → Cliquez sur le cœur sous une publication

---

## 🗄️ Base de données

Le fichier `database.sql` crée automatiquement les tables suivantes :

| Table | Description |
|---|---|
| `utilisateurs` | Comptes utilisateurs (pseudo, email, mot de passe, profil) |
| `publications` | Posts avec texte et image optionnelle |
| `amis` | Relations d'amitié avec statut (en_attente / accepte) |
| `messages_prives` | Messages entre utilisateurs avec indicateur de lecture |
| `likes` | Likes sur les publications |
| `notifications` | Alertes automatiques (ami, like, message) |

---




## 📝 Licence

Ce projet a été réalisé dans le cadre d'un cours à l'**École Normale Supérieure de l'Enseignement Technique de Mohammedia** — Université Hassan II de Casablanca.

---

> 💡 **Astuce** : Pour tester rapidement, créez deux comptes différents dans deux onglets ou navigateurs pour tester l'amitié, la messagerie et les notifications.
