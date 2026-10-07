# WMS

Warehouse Management System built with Laravel.

## Setup

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run prod
```

Configure `.env` with the database credentials, mail settings, and CMS sync values:

```env
COURIER_SAAS_URL=
WMS_SYNC_TOKEN=
WMS_SYNC_TIMEOUT=10
```

Point the web server document root to `public`.
