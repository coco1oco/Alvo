# Alvo Finance Manager

Aim. Save. Achieve. 

Alvo is a modern, lightweight, and professional personal finance manager designed to give you a clear, birds-eye view of your financial health. Inspired by modern fintechs like Monzo and Revolut, Alvo aims to be clean, airy, and intuitive.

## Features

- **Dashboard:** At-a-glance net worth, recent transactions, and cash flow visualizations.
- **Accounts:** Manage multiple wallets, bank accounts, and credit cards in one place.
- **Transactions:** Record, categorize, and track expenses and income.
- **Budgets:** Set spending limits and track your progress to avoid overspending.
- **Secure Authentication:** Integrated with [Clerk](https://clerk.com/) for fast, secure, passwordless (or standard) authentication.
- **Dark Mode:** A meticulously crafted Onyx & Graphite dark mode that's easy on the eyes.

## Tech Stack

- **Backend:** [Laravel 11](https://laravel.com/) 
- **Frontend:** [Vue 3](https://vuejs.org/) (Composition API) + [Vite](https://vitejs.dev/)
- **Styling:** CSS Custom Properties + Tailwind CSS v4 Engine
- **Auth:** Clerk + Custom JWT Verification Middleware
- **Database:** MariaDB

## Local Development Setup

To run Alvo locally, you'll need PHP 8.2+, Composer, Node.js, and a Clerk account.

### 1. Clone the repository
```bash
git clone https://github.com/<your-username>/Alvo.git
cd Alvo
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Variables
Copy the example environment file:
```bash
cp .env.example .env
```
Generate your Laravel application key:
```bash
php artisan key:generate
```

You must also configure your Clerk credentials in the `.env` file. Create a Clerk application and retrieve your keys:
```env
VITE_CLERK_PUBLISHABLE_KEY=pk_test_...
CLERK_SECRET_KEY=sk_test_...
```

### 4. Database Setup
Run the database migrations to build your schema:
```bash
php artisan migrate
```

*(Optional) Seed the database with sample data:*
```bash
php artisan db:seed
```

### 5. Run the Application
Start the Laravel backend and Vite frontend servers concurrently:
```bash
composer dev
```
Access the application in your browser at `http://localhost:8000`.

## Architecture & Design

Alvo strictly enforces a bespoke Design System (`docs/design.md`) built around CSS custom variables and the Geist font family. The UI leverages glassmorphism and subtle micro-animations to create a premium, native-app feel within the browser.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
