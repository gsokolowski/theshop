# Release workflow

This document describes how **develop → stage → main → tag** fits together with **staging** and **production** deployments, and what CI/CD is expected to do.

---

## Branches and roles

| Branch | Role |
|--------|------|
| `feature/*` (optional) | Short-lived work |
| `develop` | Day-to-day integration |
| `stage` | Line deployed to **staging** (EC2 or equivalent); last check before production |
| `main` | Release line; only moves forward when you are ready to cut a release |
| Tag `v*` (e.g. `v1.2.0`) | Points at a commit on `main`; triggers **production** deploy |

---

## Day-to-day flow

1. **Develop** on `feature/x` or directly on `develop`.
2. Merge into **`develop`** (prefer PRs): run tests locally; CI runs on the PR.
3. When `develop` is stable enough to see on the **staging** environment: open PR **`develop` → `stage`** and merge.  
   - **Push to `stage`** should trigger CI: build, tests, **deploy to staging**.
4. When staging looks good: open PR **`stage` → `main`** and merge.  
   - **Push to `main`** should trigger CI: build and tests **only** (no production deploy).
5. **Release:** on your machine, on `main`, create and push an annotated tag:
   ```bash
   git checkout main
   git pull origin main
   git tag -a v1.2.0 -m "Release 1.2.0"
   git push origin v1.2.0
   ```
6. **Push tag** triggers CI: tests (recommended) → **deploy to production** at that exact commit.

Keep merges **forward** (`develop` → `stage` → `main`) so history stays clear.

---

## First-time Git setup (local)

```bash
git remote -v   # confirm origin → GitHub

git checkout main
git pull origin main

git checkout -b develop
git push -u origin develop

git checkout -b stage
git push -u origin stage

# Optionally align stage with develop initially
git merge develop
git push origin stage
```

Feature branch example:

```bash
git checkout develop
git pull origin develop
git checkout -b feature/my-task
# ... work and commit ...
git push -u origin feature/my-task
# Open PR: feature/my-task → develop
```

---

## Promotion summary

| Step | Action |
|------|--------|
| Integrate work | PR **feature → develop** (or commit on `develop`) |
| Staging | PR **develop → stage** |
| Release line | PR **stage → main** |
| Go live | **Tag on `main`** and `git push origin v1.2.0` |

---

## What CI/CD should do (GitHub Actions)

Use separate workflows or one workflow with clear `if:` conditions.

| Trigger | Expected behavior |
|---------|-------------------|
| **Pull request** to `develop`, `stage`, or `main` | Install deps, build backend and frontend, run tests (block merge if you use branch protection) |
| **Push** to `stage` | Tests + **deploy to staging** |
| **Push** to `main` | Tests only — **no production deploy** |
| **Push** tag matching `v*.*.*` | Tests + **deploy to production** |

Example trigger ideas:

- Staging: `on.push.branches: [stage]`
- Production: `on.push.tags: ['v*.*.*']`

Store secrets (e.g. `STAGING_HOST`, `PRODUCTION_HOST`, `SSH_PRIVATE_KEY`) in the repository or organization settings.

---

## GitHub settings (recommended)

- **Branch protection** on `main` (and optionally `stage`): require PR before merge, require passing CI, avoid force-push where practical.
- Document required secrets for deploy jobs.

---

## Servers

- **Staging:** deploys from branch **`stage`** (server checks out `stage` and pulls on each deploy).
- **Production:** deploys from **tags** (e.g. `git fetch --tags` and `git checkout v1.2.0` in the deploy script, or pass the tag name from the workflow).

---

## CI event matrix

| Event | Tests | Staging deploy | Production deploy |
|-------|-------|----------------|---------------------|
| PR to develop / stage / main | Yes | No | No |
| Push to `stage` | Yes | Yes | No |
| Push to `main` | Yes | No | No |
| Push tag `v*.*.*` | Yes | No | Yes |

---

## Hotfixes (optional)

For urgent production fixes: branch from `main`, fix, PR to `main`, tag a patch (e.g. `v1.2.1`), then merge or cherry-pick into `develop` and `stage` so branches do not drift.

---

## Related

- Environment-specific URLs and secrets belong in `.env` on each server and in CI secrets — never commit real credentials.
- When the project adds `.github/workflows/`, keep their triggers aligned with this document.
