<template>
    <div class="row">
        <Spinner :store="productsStore" /> <!-- pass the productsStore to the Spinner component -->
        <ProductsListItem v-for="product in productsStore.products" 
        :key="product.id" 
        :product="product"/> <!-- pass the product propsto the ProductsListItem component -->
    </div>
</template>

<script setup>
    // import the useProductsStore
    import { useProductsStore } from '../../stores/useProductsStore.js'
    import ProductsListItem from './ProductsListItem.vue'
    import Spinner from '../layouts/Spinner.vue'
    import { onMounted } from 'vue';

    // define the store variable and import the useProductsStore
    const productsStore = useProductsStore()
    
    // call fetchAllProducts when component mounts to fetch the products from the API
    onMounted(() => {
        productsStore.fetchAllProducts()
    })
</script>