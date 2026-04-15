import router from '../router/index.js'

/**
 * Navigate to login with optional ?redirect= so user returns after auth (SPA, no full reload).
 * Only allows same-origin paths starting with / (no protocol-relative URLs).
 */
export function redirectToLogin() {
  const raw = router.currentRoute.value?.fullPath || '/'
  const safe =
    typeof raw === 'string' && raw.startsWith('/') && !raw.startsWith('//') && raw !== '/login'
      ? raw
      : undefined
  if (safe) {
    router.push({ path: '/login', query: { redirect: safe } })
  } else {
    router.push('/login')
  }
}
