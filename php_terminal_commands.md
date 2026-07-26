# Common Terminal Commands for Running and Managing PHP & Laravel Projects

This guide covers the essential terminal commands used to run, build, and maintain standard PHP and Laravel projects.

---

## 🌐 Running Local Development Servers

Depending on the type of PHP project, choose one of the following commands to start a local server:

### 1. Plain / Vanilla PHP Project

Runs PHP's built-in web server. Execute this command in your project's root folder:

```bash
php -S localhost:8000
```

_Access your project in the browser at `http://localhost:8000`._

### 2. Laravel Projects

Laravel provides its own CLI tool, `artisan`, to start the development server:

```bash
php artisan serve
```

_Access your application at `http://127.0.0.1:8000`._

### 3. Laravel + Frontend Bundling (Vite)

For Laravel projects that use Vue, React, or modern CSS/JS bundles via Vite:

```bash
npm run dev
```

_Runs the hot-reloading development server alongside the backend._

---

## 📦 Dependency & Package Management (Composer)

Composer is PHP's package manager, similar to npm/yarn for Node.js.

| Command                                 | Description                                                                                         |
| :-------------------------------------- | :-------------------------------------------------------------------------------------------------- |
| `composer install`                      | Installs the dependencies listed in `composer.lock` (use when cloning a project).                   |
| `composer update`                       | Updates all packages to their latest compatible versions based on `composer.json`.                  |
| `composer require <package-name>`       | Downloads and adds a new package to your project.                                                   |
| `composer require --dev <package-name>` | Adds a package only for development environments (e.g., testing libraries).                         |
| `composer dump-autoload`                | Rebuilds the autoloader classmap. Use this if you add new PHP classes that aren't being autoloaded. |

---

## 🗄️ Database Management (Laravel Migrations)

Artisan provides powerful database schema management tools.

```bash
# Run all pending database migrations
php artisan migrate

# Rollback the last database migration batch
php artisan migrate:rollback

# Wipe the database and re-run all migrations from scratch
php artisan migrate:fresh

# Seed the database with fake/sample data (runs DatabaseSeeder)
php artisan db:seed

# Wipe, migrate, and seed all in one command
php artisan migrate:fresh --seed
```

---

## 🧹 Cache & Optimization Utilities (Laravel)

Laravel caches configurations, routes, and views for performance. During development, you may need to clear these caches if changes aren't showing up.

```bash
# Clear all cached configurations, routes, views, and events
php artisan optimize:clear

# Clear configuration cache
php artisan config:clear

# Cache configurations for production speed
php artisan config:cache

# Clear route cache
php artisan route:clear

# Clear compiled Blade views
php artisan view:clear
```

---

## 🧪 Testing and Debugging

Commands to run unit and feature tests:

```bash
# Run the Laravel test suite
php artisan test

# Run PHPUnit tests directly (standard PHP projects)
./vendor/bin/phpunit
```
