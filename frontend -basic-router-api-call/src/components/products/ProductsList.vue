<template>
    <div>
        <h2>All Products</h2>
        <!-- Display loading message -->
        <div v-if="productsStore.isLoading">Loading...</div>
        <!-- Display products when loading is complete -->
        <div v-else>

            <!-- Display all products accesses state directly, not through the getter-->
            <div v-for="product in productsStore.products" :key="product.id">
                {{ product.name }}
                {{ product.description }}
                {{ product.price }}
                {{ product.brand.name }}
            </div>

            <h2>In Stock Products Only</h2>
            <!-- Use getInStockProducts getter to display products that are in stock -->
            <div v-for="product in productsStore.getInStockProducts" :key="product.id">
                {{ product.name }}
                {{ product.description }}
                {{ product.price }}
                {{ product.brand.name }}
            </div>
        </div>
  </div>
</template>

<script setup>
    // import the useProductsStore
    import { useProductsStore } from '../../stores/useProductsStore.js'
    import { onMounted } from 'vue';

    // define the store variable and import the useProductsStore
    const productsStore = useProductsStore()
    
    // call fetchAllProducts when component mounts to fetch the products from the API
    onMounted(() => {
        productsStore.fetchAllProducts()
    })
</script>