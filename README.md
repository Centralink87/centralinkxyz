# Centralink

Centralink est mon projet Workshop : une application Symfony de gestion de transactions et de demandes, avec un espace d'administration (EasyAdmin) protégé par rôles (`ROLE_USER` / `ROLE_ADMIN`).

## Stack

- Symfony 7 / PHP 8.4 (FrankenPHP + Caddy)
- PostgreSQL 16
- EasyAdminBundle pour le back-office

## Lancer le projet avec Docker

Prérequis : Docker et Docker Compose installés.

```bash
docker compose up --build
```

L'application est ensuite disponible sur [http://localhost:8000](http://localhost:8000).

Au premier démarrage, le conteneur `app` :
- attend que la base de données Postgres soit prête (healthcheck),
- exécute automatiquement les migrations Doctrine (`doctrine:migrations:migrate`),
- installe les assets (EasyAdmin, importmap).

Aucune intervention manuelle n'est nécessaire.

## Architecture Docker

- **`app`** : conteneur principal, build à partir du `Dockerfile` (FrankenPHP/Caddy + PHP 8.4), sert l'application Symfony sur le port `8000`.
- **`db`** : conteneur PostgreSQL 16, données persistées dans le volume Docker `pgdata`.
- Les deux conteneurs communiquent via le réseau Docker interne (`app` se connecte à `db` via `DATABASE_URL=postgresql://app:app@db:5432/centralink`).

## Variables d'environnement

- `.env` : valeurs par défaut génériques, committées (convention Symfony), sans secret réel.
- `.env.local` : overrides locaux (non commités, ignorés par git) — utilisé uniquement si vous lancez l'app hors Docker (`symfony serve`).
- En Docker, les variables réelles (`DATABASE_URL`, `APP_SECRET`, etc.) sont injectées directement par `docker-compose.yml` et prennent toujours le dessus sur les fichiers `.env`.

## Tester la persistance des données

1. Lancer l'app et créer/modifier une donnée (ex : créer un utilisateur ou une transaction depuis l'application).
2. Arrêter les conteneurs sans supprimer le volume :
   ```bash
   docker compose down
   ```
3. Relancer :
   ```bash
   docker compose up
   ```
4. Vérifier que la donnée créée à l'étape 1 est toujours présente — le volume `pgdata` persiste les données de Postgres entre les redémarrages.

## Lancer hors Docker (développement local)

```bash
composer install
php bin/console doctrine:migrations:migrate
symfony serve
```

Nécessite une instance PostgreSQL locale et un `.env.local` avec votre propre `DATABASE_URL`.

## Tests

```bash
php bin/phpunit
```
