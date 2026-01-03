<!-- Home component -->
<template>
    <h1>Home Page</h1>
    <div class="row my-5">
        <div class="col-md-8">
            <!-- Display the ProductsList component -->
            <Sidebar />
            <ProductsList />
        </div>
    </div>
    
</template>

<script setup>
    import Sidebar from './layouts/Sidebar.vue'
  import ProductsList from './products/ProductsList.vue'
</script>