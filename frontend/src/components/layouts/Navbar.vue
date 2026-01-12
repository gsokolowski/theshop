<!-- Navbar component -->
<template>
  <header>
      <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
          <div class="container">
              <router-link class="navbar-brand" to="/">
                  <a class="navbar-brand" href="#">The Shop</a>
              </router-link>                
              <div class="collapse navbar-collapse" id="navbarNav">
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
                              <span class="nav-link">
                                  <i class="bi bi-person-fill"></i> Hi {{ authStore.getUser?.name }}
                              </span>
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
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const cartStore = useCartStore()
const authStore = useAuthStore()
const router = useRouter()

const cartItemsCount = computed(() => cartStore.cartItems.length)

const handleLogout = async () => {
  try {
      await authStore.logout()
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