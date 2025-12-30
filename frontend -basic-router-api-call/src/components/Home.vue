<!-- Home component -->
<template>
    <h1>Home Page</h1>
    <div class="row my-5">
        <div class="col-md-8">
            <!-- Display the ProductsList component -->
            <ProductList />
        </div>
    </div>
</template>

<script setup>
import ProductList from './products/ProductsList.vue'
</script>