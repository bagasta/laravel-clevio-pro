# Clevio PRO — Laravel Breeze + n8n Chat UI

Simple Laravel 12 + Breeze app that:
- Authenticates users with Laravel’s `users` table (SQLite by default).
- Reads agents from an existing Postgres database (Prisma tables `"User"` and `"Agent"`).
- Embeds the n8n Chat widget on the dashboard and provides a full chat page per agent.
- Optionally proxies agent “run/warm” calls to your agent service.

This README explains how to set it up and run locally.

---

## Quick Start

1) Prerequisites
- PHP 8.2+ and Composer 2+
- Node.js 20+ and npm
- Postgres (your Prisma database that contains `"User"` and `"Agent"`)
- An n8n instance with a Chat Trigger webhook URL

2) Install
```bash
git clone <your-repo-url> clevio-pro-laravel
cd clevio-pro-laravel

composer install
npm install

cp .env.example .env
php artisan key:generate
```

3) Configure `.env`
- App URL (dev):
  - `APP_URL=http://localhost:8000`

- Authentication DB (default SQLite):
  - Keep `DB_CONNECTION=sqlite` (default).
  - A `database/database.sqlite` file is used for users, cache, and jobs.

- Prisma/Agents DB (Postgres):
  - Set `DATABASE_URL=postgresql://USER:PASS@HOST:5432/DBNAME` to your Prisma Postgres where `"User"` and `"Agent"` live.
  - Models `PrismaUser` and `Agent` explicitly use the `pgsql` connection which reads from `DATABASE_URL`.

- n8n Chat widget:
  - `VITE_N8N_WEBHOOK_URL="https://your-n8n/webhook/XXXXXXXX/chat"`
  - In n8n, set Allowed Origins to `http://localhost:8000` (and your production domain later).

- Agent service proxy (optional; enables Run/Warm actions):
  - `AGENT_RUN_BASE_URL="http://localhost:8000"` or your agent service base URL
  - `OPENAI_API_KEY="..."` (forwarded to the agent service when running)

4) Migrate and Seed (for auth DB)
```bash
php artisan migrate
php artisan db:seed   # creates Test User: test@example.com / password
```

5) Run in development
- Easiest: one command that runs PHP server, queue worker, logs, and Vite:
  ```bash
  composer run dev
  ```
- Or run them individually:
  ```bash
  php artisan serve        # http://localhost:8000
  php artisan queue:listen
  npm run dev
  ```

Open http://localhost:8000/login and sign in with:
- Email: `test@example.com`
- Password: `password`

Go to http://localhost:8000/dashboard to see agents and the n8n chat bubble.

---

## How Data Is Wired
- `users` (auth) live in SQLite by default. You may switch `DB_CONNECTION=pgsql` to use Postgres for auth as well if you prefer.
- `PrismaUser` and `Agent` read from the Postgres connection `pgsql` (configured via `DATABASE_URL`). These expect Prisma tables `"User"` and `"Agent"` to already exist.
- Agent “Run/Warm” buttons call backend routes that proxy to your agent service at `AGENT_RUN_BASE_URL`.

Relevant files:
- config: `config/database.php`, `config/services.php`
- models: `app/Models/User.php`, `app/Models/PrismaUser.php`, `app/Models/Agent.php`
- controllers: `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/AgentController.php`
- views: `resources/views/dashboard.blade.php`, `resources/views/agents/chat.blade.php`
- frontend: `resources/js/app.js`, `resources/js/chat.js`, `resources/js/agent-chat-page.js`, `resources/js/agent-bubble.js`

---

## n8n Chat Setup
In your n8n workflow:
1. Use the Chat Trigger node and activate the workflow.
2. Copy the Chat webhook URL into `.env` as `VITE_N8N_WEBHOOK_URL`.
3. Add `http://localhost:8000` to Allowed Origins so the widget can load in dev.
4. Optional: enable streaming responses.

The widget is initialized from `resources/js/chat.js` and loads automatically on the dashboard if `VITE_N8N_WEBHOOK_URL` is set.

---

## Production
```bash
npm run build
php artisan config:cache
php artisan route:cache
```
Serve via Nginx/Apache and ensure correct `APP_URL`, `DATABASE_URL`, and widget/agent service env vars.

---

## Troubleshooting
- No agents listed: verify `DATABASE_URL` points to the Postgres DB that contains Prisma tables `"User"` and `"Agent"`.
- Chat bubble doesn’t appear: make sure `VITE_N8N_WEBHOOK_URL` is set and your n8n Allowed Origins includes `http://localhost:8000`.
- Run/Warm fails: set `AGENT_RUN_BASE_URL` and `OPENAI_API_KEY`, and ensure your agent service is reachable. In dev, the full chat page also tries direct calls to `http://localhost:8000` and falls back to the proxy.

Happy building! 🎉
