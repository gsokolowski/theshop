# Development Preferences

## Vue.js / Frontend
- Use `reactive` instead of `ref` for objects/arrays wherever possible
- Use `ref` only for primitives (booleans, strings, numbers) when needed
- Always mark code changes with `✅ ADDED`, `✅ CHANGED`, `✅ REMOVED` indicators

## Validation
- Keep all validation logic on Laravel backend (single source of truth)
- Frontend should only display backend validation errors
- Use `novalidate` attribute on forms to disable HTML5 validation

## Code Style
- Prefer simple, minimal solutions
- Mark all changes clearly when working on files