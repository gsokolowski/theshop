# Development Preferences

> **For AI assistants:** These preferences are mandatory. Cursor loads the detailed rules from [`.cursor/rules/`](.cursor/rules/) automatically based on the files you edit. This page is the human-facing index; keep it in sync when you add or rename rule files.

## Rule files (authoritative detail)

| Area | File |
|------|------|
| Core (always applied) | `.cursor/rules/core-principles.mdc` |
| Vue / Pinia / frontend JS | `.cursor/rules/frontend.mdc` |
| Laravel API JSON shape | `.cursor/rules/laravel-api-responses.mdc` |
| Laravel controllers (admin + API) | `.cursor/rules/laravel-controllers.mdc` |
| Form requests | `.cursor/rules/laravel-form-requests.mdc` |
| PHP test docblocks | `.cursor/rules/test-method-comments.mdc` |

## Release & deployment

- **CI/CD and tag-based production deploys:** [docs/CICD.md](docs/CICD.md) (see document for current `main` + `v*` model).

## Repository documentation

- **Monorepo entry point:** [README.md](README.md) at the repository root (clone the repo and open that folder first).

## Quick summary

- **Frontend:** `reactive` for objects/arrays; `ref` for primitives and DOM refs; `computed` for store-derived state; async API calls with try/catch/finally; Spinner, toasts, Bootstrap; backend owns validation—UI shows API errors only.
- **Backend:** Form requests for validation; separate DB queries from view/response assembly; never take `user_id` from the client when the user is authenticated; JSON API responses follow the shared `{ message, data, error?, status? }` shape.
