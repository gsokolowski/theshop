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
    import { computed } from 'vue'
    
    const cartStore = useCartStore()
    
    const cartItemsCount = computed(() => cartStore.cartItems.length)

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