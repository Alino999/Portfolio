<<<<<<< HEAD
# Portfolio Laravel Professionnel

Portfolio personnel moderne, multilingue et administrable construit avec Laravel, Bootstrap et Blade.

## Fonctionnalités principales

- Site public moderne et responsive
- Dashboard administrateur
- Authentification Laravel Breeze
- Gestion du profil, expériences, compétences, projets, services, certifications, articles et contact
- Multilingue FR/EN
- Paramètres du site et SEO de base
- Gestion des CV, modèles et document generation (architecture compatible PDF/Word)
- Sécurité de base avec middleware, validation et rôle admin

## Prérequis

- PHP 8.2+
- Composer
- MySQL/MariaDB ou SQLite
- Node.js/NPM

## Installation rapide

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

## Identifiants de démonstration

- Email : admin@portfolio.test
- Mot de passe : password

## Commandes utiles

```bash
php artisan migrate
php artisan db:seed
php artisan route:list
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan test
```

## Collecte quotidienne de veille cybersécurité

La commande `articles:collect-cybersecurity` collecte les publications des trois
derniers jours depuis les flux officiels du CISA et du CERT-FR. Elle conserve
uniquement un titre, un court résumé et un lien vers la source. Les doublons sont
ignorés et chaque élément est ajouté en brouillon : il faut le relire et le publier
depuis l’administration pour le rendre visible sur le blogue.

Les sources et la période sont configurées dans `config/cybersecurity_feeds.php`.
Pour lancer une collecte manuelle :

```bash
php artisan articles:collect-cybersecurity
```

En développement local, le planificateur Laravel peut être démarré avec :

```bash
php artisan schedule:work
```

En production, configurez une tâche cron exécutée chaque minute, depuis le dossier
de l’application :

```cron
* * * * * cd /chemin/vers/portfolio && php artisan schedule:run >> /dev/null 2>&1
```

L’hébergement doit autoriser les tâches cron pour que la collecte quotidienne
s’exécute automatiquement.

## Structure principale

- `app/Models` : modèles métier
- `app/Http/Controllers` : contrôleurs public et admin
- `database/migrations` : schéma relationnel du portfolio
- `database/seeders` : données de démonstration
- `resources/views/public` : interface publique
- `resources/views/admin` : dashboard et administration
- `resources/lang` : traductions FR/EN

## Architecture retenue

Le projet suit une logique MVC standard Laravel, avec une séparation claire entre l’interface publique et le back-office. Les contenus sont stockés dans des tables relationnelles et les éléments traduisibles utilisent des données structurées depuis la base.

## Déploiement

Pour un hébergement PHP Laravel classique :

1. Déployer le projet sur le serveur
2. Configurer le `.env` avec la base de données
3. Lancer `composer install --no-dev`
4. `php artisan migrate --force`
5. `php artisan db:seed --force`
6. `php artisan storage:link`
7. Compiler les assets front : `npm install && npm run build`

## État du projet

Cette base couvre la structure fonctionnelle du portfolio, l’authentification et le site public/admin. Elle peut être enrichie progressivement par CRUD successifs pour chaque section du cahier des charges.
=======
# Portfolio
pour mon portfolio
>>>>>>> 4e1e5055ed4b2ef065deb3a547de9f995257d7fd
