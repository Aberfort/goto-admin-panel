# LinkFleet — Frontend

React 18 + TypeScript SPA, built with Vite. See the [root README](../README.md) for the overall architecture.

## Requirements

- Node.js >= 20

## Setup

```bash
cp .env.example .env   # VITE_API_URL=http://localhost:8000 (empty = same-origin)
npm install
npm run dev
```

Open http://localhost:3000. Needs the backend running (see [`backend/README.md`](../backend/README.md)).

## Scripts

| | |
|---|---|
| `npm run dev` | Dev server with HMR |
| `npm run build` | Type-check + production build to `dist/` |
| `npm run preview` | Serve the production build locally |
| `npm run typecheck` | `tsc -b --noEmit` |
| `npm run lint` | ESLint |
| `npm run test` | Vitest (watch mode) — use `npx vitest run` for a single pass |

## Structure

```
src/
  api/          Typed axios wrappers, one file per backend resource
                (client.ts holds the shared axios instance)
  contexts/     AuthContext - bearer token in localStorage, exposes user.is_demo
  pages/        One file per route (SitesPage, SiteLinksPage, LinkFormPage,
                AnalyticsDashboardPage, LoginPage, RegisterPage)
  components/   Shared UI (currently just Navbar)
  types/        Shared TypeScript interfaces, mirroring the backend's API shapes
```

`AnalyticsDashboardPage` (and its `@mui/x-charts` dependency, the single heaviest
package in the app) is lazy-loaded via `React.lazy` — see `App.tsx` — so it's
not in everyone's initial bundle.

## Auth

Bearer token in `localStorage`, sent as `Authorization: Bearer <token>` on every
request (`api/client.ts` + `AuthContext`). No cookies, no CSRF token dance —
matches the backend's stateless Sanctum setup.

`user.is_demo` (surfaced by `AuthContext`) drives read-only UI gating in
`SitesPage`/`SiteLinksPage` — hidden/disabled create/edit/delete controls, plus
an explanatory `Alert`. This is a UX nicety only; the actual enforcement is
server-side (see the backend README).

## Tests

```bash
npx vitest run
```

Vitest + Testing Library, mocking the `api/*` modules directly rather than the
HTTP layer. Covers `AuthContext`'s login/logout state transitions, `LoginPage`
validation and submit flow, and `SitesPage`/`SiteLinksPage` listing, creation,
toggling, and demo-readonly gating.
