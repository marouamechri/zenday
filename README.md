# 🌿 Moments Positifs

ZenDay est une API REST développée en Symfony qui permet aux utilisateurs de capturer, organiser et revoir leurs moments positifs au quotidien. L’objectif est de favoriser le bien-être en mettant en avant les émotions positives à travers une application simple et accessible.

---

## ✨ Fonctionnalités

### 👤 Utilisateur
- `POST /api/register` — Créer un compte utilisateur
- `POST /api/login` — Se connecter et obtenir un token JWT
- `GET /api/me` — Récupérer les informations de son profil connecté

### 🌿 Moments
- `POST /api/moments` — Ajouter un moment (titre, description, humeur, tag, localisation)
- `GET /api/moments` — Lister tous les moments de l’utilisateur
- `GET /api/moments/{id}` — Voir le détail d’un moment
- `PUT /api/moments/{id}` — Modifier un moment
- `DELETE /api/moments/{id}` — Supprimer un moment

### 🧠 Tags & Humeurs
- `GET /api/tags` — Récupérer la liste des tags positifs (`gratitude`, `détente`, `sourire`, etc.)
- `GET /api/humeurs` — Récupérer la liste des humeurs (`joie`, `calme`, `émerveillement`, etc.)

### 💌 Bonus
- `GET /api/moments/random` — Voir un moment positif aléatoire pour se remonter le moral

## ⚙️ Installation

### Prérequis
- PHP 8.1+
- Composer
- Symfony CLI (optionnel mais recommandé)
- MySQL / MariaDB ou PostgreSQL
- Un environnement web local (XAMPP, Laragon, Docker…)

### Étapes

git clone https://github.com/marouamechri/zenday
cd moments-positifs
composer install
cp .env .env.local

# Configure ta BDD dans .env.local (ex: DATABASE_URL=mysql://user:pass@127.0.0.1:3306/zenday_db

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Génération des clés JWT pour l'authentification :

- dir -p config/jwt
- openssl genrsa -out config/jwt/private.pem -aes256 4096
- openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

- Ajoute ceci dans ton fichier .env.local :
    JWT_PASSPHRASE=ta-passphrase


# 🔐 Sécurité

L'API utilise :

🔑 Authentification via JWT

🧾 Validation des entrées via les formulaires Symfony

🔒 Accès restreint aux routes avec les rôles et les tokens

# 📁 Structure du projet

├── config/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Repository/
│   └── Security/
├── migrations/
├── public/
├── .env
└── README.md

## ✅ Fonctionnalités terminées

- ✅ Authentification (register, login, forgotPassword)
- ✅ Ajout, édition et suppression de moments positifs
- ✅ Système de tags et d’humeurs
- ✅ API REST sécurisée (JWT)
- ✅Filtres par humeur et tags


# 📌 TODO
 CRUD Moments 

 - Voir les statistiques de l’utilisateur : nombre de moments par semaine, humeur dominante, etc.

 - Pagination & recherche

 - Interface frontend (Angular ? Mobile ?)

 

 # 👩‍💻 Développeuse
- 👤 Mechri Maroua
📫 Contact : marwa.mechri@gmail.com
