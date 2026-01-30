<template>
  <Navbar />
  <div class="container">
      <router-view />
      <Footer />
  </div>
</template>

<script setup>
  import Navbar from './components/layouts/Navbar.vue'
  import Footer from './components/layouts/Footer.vue'
  import { useAuthStore } from './stores/useAuthStore'
  import { useCartStore } from './stores/useCartStore'
  import { onMounted } from 'vue'

  const authStore = useAuthStore()
  const cartStore = useCartStore()

  onMounted(async () => {
    // If user is already logged in (page refresh), load their cart
    if (authStore.getIsUserLoggedIn) {
      try {
        await cartStore.fetchCart()
      } catch (error) {
        // Error handling is done in the store
        console.error('Failed to load cart on app mount:', error)
      }
    }
  })
</script>


<style scoped>
</style>
