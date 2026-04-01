/**
 * API base URL (includes /api/v1), no trailing slash.
 * Set VITE_API_BASE_URL in .env.development / .env.production (see .env.example).
 */
export const apiBaseUrl = (import.meta.env.VITE_API_BASE_URL || '').replace(/\/$/, '')
