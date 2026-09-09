# LinkFleet — Backend

Laravel 12 API: multi-tenant Sites/Links, the `/r/{code}` redirect endpoint, and click analytics. See the [root README](../README.md) for the overall architecture.

## Requirements

- PHP >= 8.2
- Composer
- SQLite (zero setup, recommended for local dev) or MySQL

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite   # only if using the default sqlite connection
php artisan migrate --seed
php artisan serve
```

The API is now at `http://localhost:8000`. Seeding creates a read-only demo account (`demo@linkfleet.app` / `demo12345`, see [`DemoUserSeeder`](database/seeders/DemoUserSeeder.php)) plus a couple of sample sites/links/clicks so there's something to look at (see [`DemoDataSeeder`](database/seeders/DemoDataSeeder.php)). Both seeders are idempotent — safe to re-run.

To use MySQL instead, uncomment the `DB_*` block in `.env.example` (matches `docker-compose.yml`).

## Auth model

Sanctum bearer tokens, fully stateless — no session cookies, no CSRF dance. `POST /api/login` / `/api/register` return a token; send it as `Authorization: Bearer <token>` on everything else.

Ownership is enforced by [Policies](app/Policies) (`SitePolicy`, `LinkPolicy`), not ad-hoc controller checks — a user can only ever see/edit/delete their own sites and links. Demo-account read-only enforcement is a single [`Gate::before()`](app/Providers/AppServiceProvider.php) hook, so it applies uniformly regardless of resource type.

## API

| Method | Path | Auth | |
|---|---|---|---|
| POST | `/api/register` | — | Gated by `REGISTRATION_ENABLED` |
| POST | `/api/login` | — | |
| POST | `/api/logout` | ✓ | |
| GET | `/api/user` | ✓ | |
| GET | `/api/config` | — | `{ registration_enabled }` |
| GET/POST | `/api/sites` | ✓ | |
| GET/PUT/DELETE | `/api/sites/{site}` | ✓ | |
| GET/POST | `/api/sites/{site}/links` | ✓ | |
| POST | `/api/sites/{site}/links/import` | ✓ | CSV upload, see below |
| GET/PUT/DELETE | `/api/links/{link}` | ✓ | |
| PATCH | `/api/links/{link}/toggle` | ✓ | flips `is_active` |
| GET | `/api/sites/{site}/analytics` | ✓ | rolled up across all its links |
| GET | `/api/links/{link}/analytics` | ✓ | single link |
| GET | `/r/{code}` | — | the actual redirect (302 + click logging) |
| POST | `/r/{code}` | — | password gate submit |
| GET | `/qr/{code}.svg` | — | QR code for the short link |

### Link options

- **Expiry** (`expires_at`): after it passes, `/r/{code}` answers `410 Gone` and logs nothing.
- **Password** (`password`): visitors get a server-rendered gate first; the click is only recorded once they're through. The hash is never returned by the API — read `has_password` instead. On update, omitting the key leaves the password unchanged; sending it empty removes it.
- **QR codes** are public and generated on the fly, so they can be embedded directly as `<img src="…/qr/{code}.svg">`.

### CSV import

Expects a header row containing `target_url`, optionally `short_code`:

```csv
target_url,short_code
https://example.com/promo,summer-sale
https://example.com/docs,
```

Rows are validated individually and capped at 1000 per file — a bad row is skipped and reported back with its line number and reason rather than failing the whole upload. A blank `short_code` is auto-generated.

## Tests

```bash
vendor/bin/phpunit
```

38 Feature/Unit tests — auth flow, ownership boundaries (cross-user 403s, demo-account write blocks), the redirect+click-logging path, analytics aggregation. `phpunit.xml` runs against an in-memory SQLite database, so no service container/setup needed.

```bash
vendor/bin/pint          # check code style
vendor/bin/pint --dirty  # fix it
```

## Structure

```
app/
  Actions/          RecordLinkClick - the redirect endpoint's core logic
  Http/Controllers/
  Http/Requests/     Validation + authorization (FormRequest::authorize())
  Models/            User, Site, Link, Click
  Policies/          Ownership checks
  Support/           UserAgentParser, ClientIp - small, framework-agnostic helpers
database/
  migrations/
  seeders/           DemoUserSeeder, DemoDataSeeder (idempotent, run on every deploy)
routes/
  api.php            JSON API, auth:sanctum-protected where noted above
  web.php            Just the /r/{code} redirect - a real browser navigation, not JSON
```
