<!-- Navbar component -->
<template>
  <header>
      <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
          <div class="container">
            <router-link 
                class="navbar-brand" 
                to="/"
                @click="handleShopClick"
            >
                The Shop
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
                          <router-link class="nav-link" aria-current="page" to="/profile">
                              <i class="bi bi-person-fill"></i> {{ authStore.getUser?.name }}
                          </router-link>
                          </li>
                          <li class="nav-item">
                          <router-link class="nav-link" aria-current="page" to="/user/orders">
                              <i class="bi bi-bag-check-fill"></i> Orders
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
import { useProductsStore } from '../../stores/useProductsStore'

import { useRouter } from 'vue-router'
import { computed } from 'vue'

const cartStore = useCartStore()
const authStore = useAuthStore()
const productsStore = useProductsStore()

const router = useRouter()

const handleShopClick = () => {
    // clear filters when user clicks on the shop navbar link
    productsStore.clearFilters()
    console.log('Filters cleared - shop clicked')
}

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