# Getting Fast at CakePHP

A step-by-step **[CakePHP 5](https://book.cakephp.org/5/en/index.html)** port of [laravel-101](https://github.com/iammikek/laravel-101) for Laravel developers learning CakePHP. Same items/categories JSON API, JWT auth, pagination, filters, and stats. **API-only** (no `/shop` UI).

**Audience:** You already know routes, Eloquent, migrations, middleware, and validation. Tables and entities stand in for Eloquent models, `bin/cake migrations migrate` for `artisan migrate`, and middleware for the HTTP kernel.

**API-only by design:** laravel-101 also includes a Blade shop at `/shop`. This repo keeps the JSON API so the CakePHP pieces stay easy to see. Pair it with [react-101](https://github.com/iammikek/react-101), [vue-101](https://github.com/iammikek/vue-101), or curl.

---

## What's Included

1. **CakePHP 5:** controllers, routes, middleware
2. **ORM:** `UsersTable`, `CategoriesTable`, `ItemsTable`
3. **Migrations:** SQLite schema via `bin/cake migrations migrate`
4. **JWT auth:** Bearer tokens (`firebase/php-jwt`) on writes and `/auth/me`
5. **Pagination:** `{ items, total, skip, limit }`
6. **Filtering:** `min_price`, `max_price`, `category_id`, `name_contains`
7. **Item stats:** `GET /items/stats/summary`
8. **JSON errors:** domain errors become `{ detail, code }`
9. **Tests:** 19 PHPUnit feature tests
10. **CI:** GitHub Actions

---

## Quick Start

### Local PHP (SQLite)

```bash
cd cakephp-101
cp config/app_local.example.php config/app_local.php
composer install
make migrate
make serve
```

Open **http://127.0.0.1:8017/** for the hello message.
**http://127.0.0.1:8017/items** returns the JSON list.

### Docker (SQLite)

```bash
docker compose up --build
```

API on **http://localhost:8017**.

### Tests

```bash
make test
```

---

## Project Structure

```
cakephp-101/
├── config/
│   ├── routes.php                 # Route map and JWT middleware
│   ├── app_local.example.php      # SQLite, salt, JWT secret
│   └── Migrations/                # users, categories, items
├── src/
│   ├── Controller/                # Health, Auth, Items, Categories
│   ├── Error/                     # JSON exception renderer
│   ├── Exception/                 # ApiException
│   ├── Middleware/                # JwtAuthMiddleware
│   ├── Model/Table/               # Users, Categories, Items
│   ├── Service/                   # JwtService
│   └── Utility/                   # Validator
├── database/                      # SQLite files (gitignored)
└── tests/TestCase/Feature/        # PHPUnit feature tests
```

---

## API endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/` | No | Hello message |
| GET | `/health` | No | Health check |
| POST | `/auth/register` | No | Register user |
| POST | `/auth/login` | No | Login (form or JSON) |
| GET | `/auth/me` | JWT | Current user |
| GET | `/categories` | No | List categories |
| GET | `/categories/{id}` | No | Show category |
| POST/PATCH/DELETE | `/categories` | JWT | Manage categories |
| GET | `/items` | No | List items (paginated, filterable) |
| GET | `/items/stats/summary` | No | Item statistics |
| GET | `/items/{id}` | No | Show item |
| POST/PATCH/DELETE | `/items` | JWT | Manage items |

Write operations require `Authorization: Bearer <token>`.

---

## Try it with curl

```bash
curl http://127.0.0.1:8017/
curl http://127.0.0.1:8017/health

curl -X POST http://127.0.0.1:8017/auth/register \
  -H 'Content-Type: application/json' \
  -d '{"email":"ada@example.com","password":"password123"}'

TOKEN=$(curl -s -X POST http://127.0.0.1:8017/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"ada@example.com","password":"password123"}' \
  | php -r 'echo json_decode(stream_get_contents(STDIN))->access_token;')

curl -X POST http://127.0.0.1:8017/categories \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"name":"Tools","description":"Hand tools"}'

curl -X POST http://127.0.0.1:8017/items \
  -H "Authorization: Bearer $TOKEN" -H 'Content-Type: application/json' \
  -d '{"name":"Widget","price":9.99}'

curl 'http://127.0.0.1:8017/items?skip=0&limit=10&min_price=1&name_contains=wid'
curl http://127.0.0.1:8017/items/stats/summary
```

---

## Environment variables

| Variable | Default | Description |
|----------|---------|-------------|
| `PORT` | `8017` | Listen port (`make serve`) |
| `APP_HOST` | `127.0.0.1` | Bind address |
| `JWT_SECRET` | `dev-only-cakephp-101-jwt-secret-key` | JWT signing secret (at least 32 bytes) |
| `SECURITY_SALT` | set in `config/app_local.example.php` | Cake security salt |
| `DEBUG` | `true` | Cake debug flag |

Copy `config/app_local.example.php` to `config/app_local.php` before the first run. SQLite files live under `database/`.

---

## Framework maps

| Laravel | CakePHP 5 (this repo) |
|---------|------------------------|
| `routes/api.php` | `config/routes.php` |
| Eloquent model | `Table` + entity |
| `artisan migrate` | `bin/cake migrations migrate` |
| `auth` middleware | `JwtAuthMiddleware` |
| Form Request classes | `App\Utility\Validator` |
| Service class | Table methods |
| PHPUnit feature tests | PHPUnit integration tests |
| Catalog Shop `/shop` | Omitted (API-only) |

---

## Quick Reference

| Task | Command |
|------|---------|
| Install | `composer install` |
| Local config | `cp config/app_local.example.php config/app_local.php` |
| Migrate | `make migrate` |
| Run local | `make serve` → http://127.0.0.1:8017 |
| Tests | `make test` |
| Docker | `make docker-up` |

---

## *-101 Family

Full family list, ports, and clone-with-submodules: **[learning-101](https://github.com/iammikek/learning-101)**. Site catalogue: [automica.io/learning-101](https://automica.io/learning-101.html).
