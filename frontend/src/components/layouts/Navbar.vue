<!-- Navbar component -->
<template>
  <header>
      <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
          <div class="container">
            <!-- ✅ CHANGED: no clearFilters — preserve catalog filters like other navigation to / -->
            <router-link class="navbar-brand" to="/">
                The Shop
            </router-link>
            <button
              class="navbar-toggler"
              type="button"
              aria-controls="navbarNav"
              :aria-expanded="navbarExpanded ? 'true' : 'false'"
              aria-label="Toggle navigation"
              @click.stop="onNavbarTogglerClick"
            >
              <span class="navbar-toggler-icon"></span>
            </button>
              <div
                id="navbarNav"
                class="collapse navbar-collapse"
                @click="onCollapseContentClick"
              >
                  <ul class="navbar-nav ms-auto">
                      <li class="nav-item">
                          <router-link class="nav-link" aria-current="page" to="/">
                              <i class="bi bi-house-door-fill"></i> Home
                          </router-link>
                      </li>
                      
                      <!-- Show Register/Login only if NOT logged in -->
                      <template v-if="!authStore.getIsUserLoggedIn">
                          <li class="nav-item">
                              <router-link class="nav-link" aria-current="page" to="/register">
                                  <i class="bi bi-person-add"></i> Register
                              </router-link>
                          </li>
                          <li class="nav-item">
                              <router-link class="nav-link" aria-current="page" to="/login">
                                  <i class="bi bi-box-arrow-right"></i> Login
                              </router-link>
                          </li>
                      </template>
                      
                      <!-- Show user name and logout if logged in -->
                      <template v-else>
                          <li class="nav-item">
                          <router-link class="nav-link" aria-current="page" to="/profile">
                              <i class="bi bi-person-fill"></i> {{ authStore.getUser?.name }}
                          </router-link>
                          </li>
                          <li class="nav-item">
                          <router-link class="nav-link" aria-current="page" to="/user/orders">
                              <i class="bi bi-bag-check-fill"></i> Orders
                          </router-link>
                          </li>
                          <!-- ✅ CHANGED: wishlist page (same path as production /user/wishlist) -->
                          <li class="nav-item">
                          <router-link class="nav-link" aria-current="page" to="/user/wishlist">
                              <i class="bi bi-heart"></i> Wishlist
                          </router-link>
                          </li>

                          <li class="nav-item">
                              <a class="nav-link" href="#" @click.prevent="handleLogout">
                                  <i class="bi bi-box-arrow-right"></i> Logout
                              </a>
                          </li>
                      </template>
                      
                      <li class="nav-item">
                          <router-link class="nav-link" aria-current="page" to="/about">
                              <i class="bi bi-info-circle-fill"></i> About
                          </router-link>
                      </li>
                      <li class="nav-item">
                          <router-link class="nav-link" aria-current="page" to="/cart">
                              <i class="bi bi-cart-fill"></i> Cart({{ cartItemsCount }})
                          </router-link>
                      </li>
                  </ul>
              </div>
          </div>
      </nav>
  </header>  
</template>

<script setup>
import { useCartStore } from '../../stores/useCartStore'
import { useAuthStore } from '../../stores/useAuthStore'

import { useRouter } from 'vue-router'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { Collapse } from 'bootstrap'

const cartStore = useCartStore()
const authStore = useAuthStore()

const router = useRouter()

const cartItemsCount = computed(() => cartStore.cartItems.length)

const navbarExpanded = ref(false)

function isMobileNavBreakpoint() {
  return Boolean(window.matchMedia?.('(max-width: 991.98px)')?.matches)
}

function syncNavbarExpandedFromDom() {
  const el = document.getElementById('navbarNav')
  navbarExpanded.value = Boolean(el?.classList.contains('show'))
}

/** Explicit toggle: first tap opens, second tap closes (same as Bootstrap collapse toggle). */
function onNavbarTogglerClick() {
  if (!isMobileNavBreakpoint()) {
    return
  }
  const el = document.getElementById('navbarNav')
  if (!el) {
    return
  }
  Collapse.getOrCreateInstance(el, { toggle: false }).toggle()
}

/** Close mobile menu after choosing a link (Bootstrap collapse stays open otherwise). */
function onCollapseContentClick(event) {
  const link = event.target.closest('a')
  if (!link || !link.classList.contains('nav-link')) {
    return
  }
  if (!isMobileNavBreakpoint()) {
    return
  }
  const el = document.getElementById('navbarNav')
  if (!el?.classList.contains('show')) {
    return
  }
  Collapse.getOrCreateInstance(el, { toggle: false }).hide()
}

let navbarCollapseEl = null

onMounted(() => {
  navbarCollapseEl = document.getElementById('navbarNav')
  if (!navbarCollapseEl) {
    return
  }
  navbarCollapseEl.addEventListener('shown.bs.collapse', syncNavbarExpandedFromDom)
  navbarCollapseEl.addEventListener('hidden.bs.collapse', syncNavbarExpandedFromDom)
})

onBeforeUnmount(() => {
  if (!navbarCollapseEl) {
    return
  }
  navbarCollapseEl.removeEventListener('shown.bs.collapse', syncNavbarExpandedFromDom)
  navbarCollapseEl.removeEventListener('hidden.bs.collapse', syncNavbarExpandedFromDom)
  navbarCollapseEl = null
})

const handleLogout = async () => {
  try {
      await authStore.logout()
      // Clear cart from localStorage to prevent user data leakage
      // This ensures User 2 doesn't see User 1's cart items when logging in on the same computer
      cartStore.clearCart(false) // Don't show toast on logout
      router.push('/login')
  } catch (error) {
      console.error('Logout error:', error)
  }
}
</script>

<style scoped>
.navbar-brand {
  font-size: 1.5rem;
  font-weight: 700;
}
.navbar-brand:hover {
  color: #007bff;
}
</style>