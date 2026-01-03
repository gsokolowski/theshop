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


</script>


<style scoped>
</style>
