<template>
    <div class="row min-vh-100">
        <Sidebar />
        <ProductsList />
    </div>
</template>

<script setup>
    import Sidebar from './layouts/Sidebar.vue'
  import ProductsList from './products/ProductsList.vue'
</script>