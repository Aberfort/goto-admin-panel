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
| GET | `/api/sites/{site}/domain` | ✓ | `{ domain: … \| null }` |
| POST | `/api/sites/{site}/domain` | ✓ | attach a custom domain, replacing any existing one |
| POST | `/api/domains/{domain}/verify` | ✓ | DNS TXT check; 422 while the record is missing |
| DELETE | `/api/domains/{domain}` | ✓ | |
| GET | `/r/{code}` | — | the actual redirect (302 + click logging) |
| POST | `/r/{code}` | — | password gate submit |
| GET | `/qr/{code}.svg` | — | QR code for the short link |
| GET/POST | `/{code}` | — | same redirect, on a verified custom domain |

### Link options

- **Expiry** (`expires_at`): after it passes, `/r/{code}` answers `410 Gone` and logs nothing.
- **Password** (`password`): visitors get a server-rendered gate first; the click is only recorded once they're through. The hash is never returned by the API — read `has_password` instead. On update, omitting the key leaves the password unchanged; sending it empty removes it.
- **QR codes** are public and generated on the fly, so they can be embedded directly as `<img src="…/qr/{code}.svg">`.

### Custom domains

A site can claim one hostname. Ownership is proven with a DNS TXT record — `_linkfleet.<host>` holding the domain's `verification_token` — checked through `Support\DnsTxtLookup`, a thin seam over `dns_get_record()` so tests never touch the network.

Once verified, that host serves the site's links at the root: `go.example.com/summer-sale` resolves the same link as `/r/summer-sale`, logs the same click, and honours the same expiry and password gate. The catch-all route is registered last in `routes/web.php` and only matches when the request's `Host` belongs to a *verified* domain, so `/`, `/up`, `/r/…` and `/qr/…` keep their meaning and an unverified or unknown host gets a 404.

Two things are deliberately **not** here, because they need a real domain to build against:

- pointing the host at the app (A/CNAME) and issuing its TLS certificate;
- per-domain short codes. `links.short_code` stays globally unique, so two sites can't both own `summer-sale`. Scoping codes per domain is the natural follow-up once a domain can actually serve traffic.

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

81 Feature/Unit tests — auth flow, ownership boundaries (cross-user 403s, demo-account write blocks), the redirect+click-logging path, analytics aggregation, custom-domain verification and host-based routing. `phpunit.xml` runs against an in-memory SQLite database, so no service container/setup needed.

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
  Models/            User, Site, Link, Click, Domain
  Policies/          Ownership checks
  Support/           UserAgentParser, ClientIp, DnsTxtLookup - small, framework-agnostic helpers
database/
  migrations/
  seeders/           DemoUserSeeder, DemoDataSeeder (idempotent, run on every deploy)
routes/
  api.php            JSON API, auth:sanctum-protected where noted above
  web.php            Redirects, QR codes and the custom-domain catch-all -
                     real browser navigation, not JSON
```
