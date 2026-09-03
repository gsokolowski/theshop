# The Shop — HTTP API reference

This document covers:

1. **Versioned JSON API** under `{APP_URL}/api/v1` — request bodies, validation, and **cURL** examples.  
2. **Other HTTP surfaces** on the same Laravel app: **[health `GET /up`](#health-check-get-up)**, **optional [Sanctum `GET /sanctum/csrf-cookie`](#sanctum-spa-csrf-cookie-optional)**, and the **session-based [admin panel](#admin-panel-web--blade)** (`routes/web.php`).

**Source of truth:** `backend/routes/api.php` (exactly **34** `Route::` entries under `Route::prefix('v1')`) for the JSON API; `backend/routes/web.php` for admin; `bootstrap/app.php` (`health: '/up'`) for the health check.

A **checklist of every `/api/v1` call** is in [Complete route index](#complete-route-index-backendroutesapiph) below.

---

## Conventions

### Base URL

All routes below are relative to:

```text
{BASE} = {APP_URL}/api/v1
```

**Example (local):** `http://127.0.0.1:8000/api/v1`  
Replace `{BASE}` in examples with your actual origin and `/api/v1` (no trailing slash on the v1 segment before individual paths — paths are like `{BASE}/products`).

### Headers (recommended for every request)

| Header | Value | Notes |
|--------|--------|--------|
| `Accept` | `application/json` | Ensures validation errors and 401/403 are JSON, not HTML redirects. |
| `Content-Type` | `application/json` | For JSON bodies. Use `multipart/form-data` for profile image upload. |
| `Authorization` | `Bearer {token}` | Required for all **`auth:sanctum`** routes. Obtain token from `POST /user/login` or Google callback handoff. |

### Authentication (Laravel Sanctum)

- **Public** routes: registration, login, product listing, email verification query, Google OAuth (browser redirect).
- **Protected** routes: group wrapped in `auth:sanctum` in `routes/api.php` — send a valid **personal access token** as `Authorization: Bearer <plainTextToken>`.
- **Logout** revokes the **current** token: `POST /user/logout` with that same `Authorization` header.

### Response shape

The API is **not** identical for every endpoint; common patterns include:

- **Success (many controllers):** `{ "message": "...", "data": { ... } }` with optional `"status": 200`.
- **Login (special case):** top-level `user`, `access_token`, and `message` (not nested only under `data`) — see [Login](#post-userlogin).
- **Validation error (422):** Laravel default: `{ "message": "...", "errors": { "field": ["..."] } }`.
- **Coupon invalid:** `{ "error": "Invalid or expired coupon" }` with `404` (no `message` key).

**Resources:** `UserResource`, `ProductResource`, `OrderResource`, `ReviewResource`, `CartResource`, `WishlistResource`, `CouponResource` — see `backend/app/Http/Resources/`.

### Rate limiting

`bootstrap/app.php` enables API throttling; limits are defined in `App\Providers\AppServiceProvider` for the `api` limiter. Intended behavior includes stricter limits on auth routes and higher limits for product reads — **verify path patterns** match your deployed prefix (`/api/v1/...` vs `api/...` in that file) if you rely on stricter auth limits.

### cURL: saving and reusing a token

```bash
# After login, extract token (example with jq):
TOKEN=$(curl -s -X POST "http://127.0.0.1:8000/api/v1/user/login" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"hihi@mail.com","password":"password"}' | jq -r '.access_token')

curl -s "http://127.0.0.1:8000/api/v1/user" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

(Use a seeded user from `UserSeeder` or your own test account; default seeded passwords in dev are often `password` where the seeder uses `Hash::make('password')`.)

---

## Public routes (no Bearer token)

### `GET` `/products` — list & filter (paginated)

**Query (optional), validated by `ListProductsRequest`:**

| Parameter | Rule |
|------------|------|
| `page` | integer ≥ 1 |
| `per_page` | integer 1–50 (default 4) |
| `category` | `categories.slug` |
| `brand` | `brands.slug` |
| `color_id` | `colors.id` |
| `size_id` | `sizes.id` |
| `search` | string, max 255 — matches product `name` / `description` (partial) |

**Example:**

```bash
curl -s "http://127.0.0.1:8000/api/v1/products?page=1&per_page=8&search=shirt" \
  -H "Accept: application/json"
```

**Response:** Laravel **paginated** `ProductResource` collection with **additional** top-level data: `categories`, `brands`, `colors`, `sizes` (facet lists for the shop UI). Standard pagination includes `data`, `links`, `meta`.

---

### `GET` `/products/{slug}` — single product

`{slug}` is the product **slug** (see `Product::getRouteKeyName()`). Loads `category`, `brand`, `colors`, `sizes`, `reviews`.

```bash
curl -s "http://127.0.0.1:8000/api/v1/products/some-product-slug" \
  -H "Accept: application/json"
```

---

### `GET` `/products/category/{category}` — filter by category

`{category}` is resolved by **slug** (`Category` route key).

**Query:** `page`, `per_page` (same caps as above). Response includes sidebar metadata and a `filter` string (category name).

```bash
curl -s "http://127.0.0.1:8000/api/v1/products/category/women?page=1&per_page=4" \
  -H "Accept: application/json"
```

---

### `GET` `/products/brand/{brand}` — filter by brand

`{brand}`: **slug** of brand.

```bash
curl -s "http://127.0.0.1:8000/api/v1/products/brand/some-brand?page=1" \
  -H "Accept: application/json"
```

---

### `GET` `/products/color/{color}` — filter by color

`{color}`: **id** of `Color` (route model binding on `Color`).

---

### `GET` `/products/size/{size}` — filter by size

`{size}`: **id** of `Size`.

---

### `GET` `/products/search/{searchTerm}` — search by path segment

`{searchTerm}` must be non-empty; otherwise `400` with `{ "message": "searchTerm parameter is required" }`.

```bash
curl -s "http://127.0.0.1:8000/api/v1/products/search/blue" \
  -H "Accept: application/json"
```

---

### `POST` `/user/register` — create account (email / password)

**Body (JSON) — `UserStoreRequest`:**

| Field | Rule |
|--------|------|
| `name` | required, string, 3–255 |
| `email` | required, email, unique |
| `password` | required, min 8 |
| `confirm_password` | required, same as `password` |

Queues a verification email job (requires queue worker if `QUEUE_CONNECTION=database`).

**Example:**

```bash
curl -s -X POST "http://127.0.0.1:8000/api/v1/user/register" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "confirm_password": "password123"
  }'
```

**Success (201):** `message` + `data` = `UserResource` user (no token — user must verify email per flow).

---

### `POST` `/user/login` — get Sanctum token

**Body — `AuthUserRequest`:** `email` (must exist in `users`), `password` (min 8).

**Success (200):**

```json
{
  "message": "User logged in successfully",
  "user": { "id": 1, "name": "...", "email": "..." },
  "access_token": "1|xxxxxxxx"
}
```

`user` is a `UserResource` payload (includes `orders`, `reviews` relations, etc. — as loaded on the model).

**Failure (401):** `{ "message": "Email or password is incorrect" }`

**Example:**

```bash
curl -s -X POST "http://127.0.0.1:8000/api/v1/user/login" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"hihi@mail.com","password":"password"}'
```

---

### `GET` `/email/verify` — verify email (signed URL)

**Not** Bearer-auth. Query parameters: **`id`**, **`signature`**, **`expires`**, plus Laravel signed URL parameters. Issued from the **verification mailable**; typically opened in a browser, not hand-crafted.

- Invalid / expired: `400` / `404` with `message`, `error`, `data`, `status` fields as implemented in `UserController::verifyEmail`.
- Success: e.g. `200` with `message` and `data` (user resource) or “already verified”.

---

### Google OAuth (browser; `web` + session)

| Method & path | Purpose |
|---------------|--------|
| `GET` `/auth/google` | Redirects to Google. |
| `GET` `/auth/google/callback` | Handles OAuth; creates/updates user; redirects to **`FRONTEND_URL/auth/google/callback?token=...`** or `?error=...`. |

Use a **browser** (or a client that follows redirects) starting at:

`http://127.0.0.1:8000/api/v1/auth/google`

Configure `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` in `.env` to match the callback URL in Google Cloud Console.

---

## Protected routes (`auth:sanctum`)

All requests require:

```http
Authorization: Bearer {access_token}
Accept: application/json
```

### `GET` `/user` — current user + token echo

**Success (200):** `message`, `user` (`UserResource`), `access_token` (bearer from request — same token for convenience in SPA).

```bash
curl -s "http://127.0.0.1:8000/api/v1/user" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

---

### `POST` `/user/logout` — revoke current token

**Success (200):** `{ "message": "User logged out successfully" }`

---

### `PUT` `/user/profile/update` — update profile (optional image)

**`UserUpdateRequest`:** all fields optional — `name`, `address`, `city`, `country`, `zip_code`, `phone_number`, `profile_image` (file `jpeg,png,jpg,gif,svg` max 2MB), `profile_completed` boolean.

**JSON-only** (no file): `Content-Type: application/json`, send only fields to change.

**With file:** `multipart/form-data` + same field names; image stored under `storage/app/public` and exposed via `public` disk.

**Success (200):** `message` + `user` (updated `UserResource`).

**cURL (multipart) example:**

```bash
curl -s -X PUT "http://127.0.0.1:8000/api/v1/user/profile/update" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -F "name=Jane" \
  -F "city=London" \
  -F "profile_image=@/path/to/photo.jpg"
```

---

### `PUT` `/user/password/update` — change password

**Body — `UserUpdatePasswordRequest`:**

| Field | Rule |
|--------|------|
| `old_password` | required |
| `new_password` | required, min 8, must match `new_password_confirmation` (Laravel `confirmed`) |
| `new_password_confirmation` | same as `new_password` |

**Success (200):** `message` + `user`; **all tokens revoked** — client must log in again.

**Failure (401):** `{ "message": "Invalid old password" }`

```bash
curl -s -X PUT "http://127.0.0.1:8000/api/v1/user/password/update" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "old_password": "password",
    "new_password": "newpassword123",
    "new_password_confirmation": "newpassword123"
  }'
```

---

### `DELETE` `/user` — delete own account (soft delete)

**Success (200):** `{ "message": "User deleted successfully" }`

---

### `GET` `/coupon/{name}` — validate coupon by code name

`{name}` is the coupon’s **`name`** field (e.g. `TESTCOUPON`).

- **200:** `message` + `data` = `CouponResource` (`id`, `name`, `discount`, `valid_until`).
- **404:** `{ "error": "Invalid or expired coupon" }` (if missing or not `isValid()`).

```bash
curl -s "http://127.0.0.1:8000/api/v1/coupon/TESTCOUPON" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

---

### `GET` `/cart` — list cart lines

**200:** `message`, `data.cart_items` = array of `CartResource` (product, color, size, quantity, `reference` string).

---

### `POST` `/cart` — add line (or merge quantity)

**Body — `CartStoreRequest`:** `product_id`, `color_id`, `size_id`, `quantity` (≥ 1). Product must be in stock (`status`).

- May **increment** an existing line with same product+color+size.
- **400** if stock exceeded or out of stock; **201** on new line; **200** on quantity merge.

```bash
curl -s -X POST "http://127.0.0.1:8000/api/v1/cart" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "product_id": 1,
    "color_id": 1,
    "size_id": 1,
    "quantity": 2
  }'
```

---

### `PUT` `/cart/{cart}` — update line quantity

`{cart}`: **id** of `Cart` row. Must belong to the user.

**Body — `CartUpdateRequest`:** `quantity` (integer ≥ 1).

```bash
curl -s -X PUT "http://127.0.0.1:8000/api/v1/cart/5" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"quantity": 3}'
```

---

### `DELETE` `/cart/{cart}` — remove line

`{cart}`: cart line **id** (owner-only).

**200:** `message`, `error: null`, `data: null`.

---

### `GET` `/wishlist` — list wishlist

**200:** `data.wishlist_items` = `WishlistResource` collection.

---

### `POST` `/wishlist` — add product

**Body — `WishlistStoreRequest`:** `product_id` (exists in `products`).

**400** if already in wishlist; **201** on create.

```bash
curl -s -X POST "http://127.0.0.1:8000/api/v1/wishlist" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"product_id": 1}'
```

---

### `DELETE` `/wishlist/{wishlist}` — remove item

`{wishlist}`: **id** of wishlist row (owner-only).

---

### `GET` `/orders` — list my orders

**200:** `data.orders` = `OrderResource` collection (with products, colors/sizes in pivot, coupon when loaded).

---

### `GET` `/orders/{order}` — single order

`{order}`: order **id**. **Policy:** must belong to the authenticated user (`OrderPolicy`).

---

### `POST` `/orders` — create orders from cart items

**Body — `OrderStoreRequest`:** `cartItems` (array, min 1). Each element:

| Field | Rule |
|--------|------|
| `product_id` | required, exists |
| `qty` | required, int ≥ 1 |
| `price` | required, numeric ≥ 0 (server recalculates totals with coupon logic) |
| `coupon_id` | optional, exists in `coupons` |
| `color_id` | required, exists in `colors` |
| `size_id` | required, exists in `sizes` |

Clears the user’s cart after success; **dispatches** `SendOrderConfirmationEmail` (queued if using database queue).

**201:** `message`, `data.user`, `data.orders` (raw order payload in controller — see `OrderController::storeUserCartItemsOrders`).

---

### `POST` `/orders/pay` — Stripe Checkout session

**Body — `PaymentCheckoutRequest`:** `cartItems` (same line fields as pay flow **except** `PaymentCheckoutRequest` does **not** require `color_id` / `size_id` in validation — only `product_id`, `qty`, `price`, optional `coupon_id`), plus:

| Field | Rule |
|--------|------|
| `success_url` | required, valid URL (Stripe appends `session_id` query param) |
| `cancel_url` | required, valid URL |

**200:** `data.url` (Checkout URL), `data.session_id`. Requires valid `STRIPE_SECRET_KEY` on the server.

**500** on Stripe API errors: `message`, `data: null`, `error` = Stripe message.

**Note:** Confirm line-item rules in `PaymentCheckoutRequest` vs checkout UI — the pay path is for **payment**; order creation with full variant info may use `POST /orders` after return.

```bash
curl -s -X POST "http://127.0.0.1:8000/api/v1/orders/pay" \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "cartItems": [
      { "product_id": 1, "qty": 1, "price": 29.99, "coupon_id": null }
    ],
    "success_url": "http://localhost:5173/success/payment/abc",
    "cancel_url": "http://localhost:5173/checkout"
  }'
```

---

### `POST` `/reviews` — create review

**Body — `ReviewStoreRequest`:** `title`, `body`, `rating` (1–5), `product_id`. One review per user per product. New reviews are **`approved: false`** until admin approves.

**201:** `data.review` = `ReviewResource`.

---

### `PUT` `/reviews/{review}` — update review

`{review}`: review **id**. **Owner only.** Re-submitted reviews reset to **unapproved** (`approved: false`).

**Body — `ReviewUpdateRequest`:** `title`, `body`, `rating`.

---

### `DELETE` `/reviews/{review}` — delete review

**Owner only.**

---

### `GET` `/reviews/check/{product_id}` — do I have a review for this product?

`{product_id}`: **integer id** of product (not slug).

**200:** `data.has_review` boolean (any review, approved or not).

```bash
curl -s "http://127.0.0.1:8000/api/v1/reviews/check/1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

---

### `POST` `/email/verification/resend` — resend verification email

Requires auth; if email already verified, returns **200** with a short message and user payload.

Otherwise queues `SendVerificationEmail`.

---

## Order of route registration (important for `{slug}`)

Some paths overlap **static** segments with **dynamic** ones. This app registers **specific** routes (e.g. `products/category/{category}`) so they are not captured by `products/{product}` (slug). See `routes/api.php` for the full order.

`Product` **route key** is **slug**; **Category** and **Brand** use **slug** in URL; **Color** and **Size** use **numeric id** in the filter routes (route model binding on `Color` and `Size`—the in-file comment mentioning “slug” for size is incorrect).

---

## Complete route index (`backend/routes/api.php`)

All paths are relative to `{BASE} = {APP_URL}/api/v1` (e.g. `http://127.0.0.1:8000/api/v1`). This table matches **`routes/api.php`**: **34** rows = **34** `Route::` entries in the `v1` group.

| # | Method | Path | Auth |
|---|--------|------|------|
| 1 | `GET` | `/user` | Sanctum |
| 2 | `POST` | `/user/logout` | Sanctum |
| 3 | `PUT` | `/user/profile/update` | Sanctum |
| 4 | `PUT` | `/user/password/update` | Sanctum |
| 5 | `DELETE` | `/user` | Sanctum |
| 6 | `GET` | `/coupon/{name}` | Sanctum |
| 7 | `GET` | `/orders` | Sanctum |
| 8 | `POST` | `/orders` | Sanctum |
| 9 | `POST` | `/orders/pay` | Sanctum |
| 10 | `GET` | `/orders/{order}` | Sanctum |
| 11 | `POST` | `/reviews` | Sanctum |
| 12 | `PUT` | `/reviews/{review}` | Sanctum |
| 13 | `DELETE` | `/reviews/{review}` | Sanctum |
| 14 | `GET` | `/reviews/check/{product_id}` | Sanctum |
| 15 | `GET` | `/cart` | Sanctum |
| 16 | `POST` | `/cart` | Sanctum |
| 17 | `PUT` | `/cart/{cart}` | Sanctum |
| 18 | `DELETE` | `/cart/{cart}` | Sanctum |
| 19 | `GET` | `/wishlist` | Sanctum |
| 20 | `POST` | `/wishlist` | Sanctum |
| 21 | `DELETE` | `/wishlist/{wishlist}` | Sanctum |
| 22 | `POST` | `/email/verification/resend` | Sanctum |
| 23 | `POST` | `/user/register` | Public |
| 24 | `POST` | `/user/login` | Public |
| 25 | `GET` | `/email/verify` | Public (signed URL) |
| 26 | `GET` | `/auth/google` | Public (browser + session) |
| 27 | `GET` | `/auth/google/callback` | Public (browser + session) |
| 28 | `GET` | `/products` | Public |
| 29 | `GET` | `/products/search/{searchTerm}` | Public |
| 30 | `GET` | `/products/{product}` | Public (slug) |
| 31 | `GET` | `/products/category/{category}` | Public (category slug) |
| 32 | `GET` | `/products/brand/{brand}` | Public (brand slug) |
| 33 | `GET` | `/products/color/{color}` | Public (color id) |
| 34 | `GET` | `/products/size/{size}` | Public (size id) |

**Routes not in the table above** are listed in the next sections: [Health](#health-check-get-up), [Sanctum CSRF (optional)](#sanctum-spa-csrf-cookie-optional), and [Admin panel](#admin-panel-web--blade).

---

## Health check: `GET /up`

Laravel registers a [health](https://laravel.com/docs/routing#health) route for uptime checks (load balancers, Kubernetes probes, monitoring).

| | |
|---|
| **Full URL** | `{APP_URL}/up` (e.g. `http://127.0.0.1:8000/up`) — **not** under `/api/v1` |
| **Source** | `bootstrap/app.php` → `->withRouting(..., health: '/up')` |
| **Method** | `GET` |
| **Auth** | None |
| **Success** | **200** if the application can boot. Body is a minimal 200 response; use for reachability, not for JSON business data. |

**Example:**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "http://127.0.0.1:8000/up"
# Expect: 200
```

---

## Sanctum SPA CSRF cookie (optional)

For **same-origin** single-page applications that use **session / cookie** authentication with the API (Sanctum “stateful” mode), Laravel’s first step is to **prime CSRF** by requesting:

| | |
|---|
| **Typical full URL** | `{APP_URL}/sanctum/csrf-cookie` (e.g. `http://127.0.0.1:8000/sanctum/csrf-cookie`) |
| **Typical method** | `GET` (issues `XSRF-TOKEN` / session cookie; see [Laravel Sanctum — SPA authentication](https://laravel.com/docs/sanctum#spa-authentication)) |
| **Auth** | None (establishes session + CSRF for subsequent `POST` from the same site) |

### Status in *this* repository

- This project **does not** call `Sanctum::routes()` in `AppServiceProvider` (or `bootstrap/app.php` `withRouting` `then:`), so **`/sanctum/csrf-cookie` is not registered** unless you add it.  
- The **Vue** client uses **Bearer tokens** from `POST /api/v1/user/login` (or the Google token redirect), not the cookie-based SPA flow—so you normally **do not** need this route for the stock frontend.

### Enabling the route (if you add cookie-based SPA auth)

Follow the current Laravel + Sanctum docs for your framework version, e.g. in `AppServiceProvider::boot()`:

```php
use Laravel\Sanctum\Sanctum;

public function boot(): void
{
    // ...
    Sanctum::routes();
}
```

Or register the equivalent `GET` route manually. Re-run `php artisan route:list` and look for `sanctum/csrf-cookie` to confirm. Configure `SANCTUM_STATEFUL_DOMAINS` in `.env` and CORS if the SPA is on a different port.

---

## Admin panel (web + Blade)

The **back office** is **not** JSON under `/api/v1`. It uses **session cookies**, **CSRF** on `POST`/`PUT`/`DELETE`, the **`admin` guard** (`App\Models\Admin`), and `App\Http\Middleware\AdminMiddleware` on the grouped routes. Controllers live under `App\Http\Controllers\Admin\`.

| Full path | Method | Purpose |
|-----------|--------|---------|
| `/admin` | `GET` | Login form (or redirect to dashboard if already logged in) |
| `/admin/auth` | `POST` | Submit admin credentials (validated by `AuthAdminRequest`) |
| `/admin/dashboard` | `GET` | Dashboard (order stats) — **requires** `admin` |
| `/admin/logout` | `POST` | Logout (invalidate session) — **requires** `admin` |
| `/admin/categories` | `GET` | List categories |
| `/admin/categories/create` | `GET` | Create form |
| `/admin/categories` | `POST` | Store |
| `/admin/categories/{category}` | `GET` | Show (slug) |
| `/admin/categories/{category}/edit` | `GET` | Edit form |
| `/admin/categories/{category}` | `PUT` | Update |
| `/admin/categories/{category}` | `DELETE` | Delete |
| `/admin/brands` | `GET` | List brands |
| `/admin/brands/create` | `GET` | Create form |
| `/admin/brands` | `POST` | Store |
| `/admin/brands/{brand}` | `GET` | Show (slug) |
| `/admin/brands/{brand}/edit` | `GET` | Edit form |
| `/admin/brands/{brand}` | `PUT` | Update |
| `/admin/brands/{brand}` | `DELETE` | Delete |
| `/admin/colors` | `GET` | List colors |
| `/admin/colors/create` | `GET` | Create form |
| `/admin/colors` | `POST` | Store |
| `/admin/colors/{color}` | `GET` | Show |
| `/admin/colors/{color}/edit` | `GET` | Edit form |
| `/admin/colors/{color}` | `PUT` | Update |
| `/admin/colors/{color}` | `DELETE` | Delete |
| `/admin/sizes` | `GET` | List sizes |
| `/admin/sizes/create` | `GET` | Create form |
| `/admin/sizes` | `POST` | Store |
| `/admin/sizes/{size}` | `GET` | Show |
| `/admin/sizes/{size}/edit` | `GET` | Edit form |
| `/admin/sizes/{size}` | `PUT` | Update |
| `/admin/sizes/{size}` | `DELETE` | Delete |
| `/admin/products` | `GET` | List products |
| `/admin/products/create` | `GET` | Create form |
| `/admin/products` | `POST` | Store (multipart, images) |
| `/admin/products/{product}` | `GET` | Show (slug) |
| `/admin/products/{product}/edit` | `GET` | Edit form |
| `/admin/products/{product}/image` | `DELETE` | Remove product image |
| `/admin/products/{product}` | `PUT` | Update |
| `/admin/products/{product}` | `DELETE` | Delete |
| `/admin/coupons` | `GET` | List coupons |
| `/admin/coupons/create` | `GET` | Create form |
| `/admin/coupons` | `POST` | Store |
| `/admin/coupons/{coupon}` | `GET` | Show |
| `/admin/coupons/{coupon}/edit` | `GET` | Edit form |
| `/admin/coupons/{coupon}` | `PUT` | Update |
| `/admin/coupons/{coupon}` | `DELETE` | Delete |
| `/admin/orders` | `GET` | List orders |
| `/admin/orders/{order}` | `PUT` | Update delivery time (`deliverd_at`) |
| `/admin/orders/{order}` | `DELETE` | Delete order (detach products first in controller) |
| `/admin/reviews` | `GET` | List reviews |
| `/admin/reviews/{review}` | `PUT` | Toggle approval (controller update) |
| `/admin/reviews/{review}` | `DELETE` | Delete review |
| `/admin/users` | `GET` | List users (active / trashed with query `filter=deleted`) |
| `/admin/users/{user}` | `DELETE` | Soft delete user |
| `/admin/users/{id}/restore` | `POST` | Restore soft-deleted user |

**Prefix:** the group is `Route::prefix('admin')` with routes like `GET /categories` — full URL path is always **`/admin/...`**, not a double `admin` prefix.

**cURL (login page):**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "http://127.0.0.1:8000/admin"
# 200 and HTML
```

**Programmatic / API clients** should not script admin CRUD with bare JSON against these URLs without handling **sessions and CSRF**; use a browser or dedicated tests (`tests/Feature/Admin/`). Credentials after seed: see [Backend README — Admin panel](../backend/README.md#admin-panel-features-and-access).

**Source of truth:** `backend/routes/web.php`.

---

## Related

- [Backend setup & admin](../backend/README.md)  
- [Frontend SPA & env](../frontend/README.md)  
- [CI/CD & releases](CICD.md)  
- [API](API.md) — HTTP routes for backend and SPA

If this document drifts from behavior, **update it** when you change `routes/api.php`, `routes/web.php`, or enable `Sanctum::routes()`. Run `php artisan route:list` to verify paths (including `up`, `sanctum/csrf-cookie` if registered, and `admin/*`).
