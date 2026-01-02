<template>
    <div class="row">
        <Spinner :store="productsStore" /> <!-- pass the productsStore to the Spinner component -->
        <ProductsListItem 
            v-for="product in productsStore.products.slice(0, data.productsPerPage)" 
            :key="product.id" 
            :product="product"/> <!-- pass the product propsto the ProductsListItem component -->

            <!-- load more products button -->
            <div class="text-center mt-4">
                <!-- if no more products to load hide the button -->
                 <!-- increment productsPerPage by productsStore.getProductsPerPage on click -->
                <button 
                    class="btn btn-outline-dark" 
                    @click="data.productsPerPage += productsStore.getProductsPerPage" 
                    v-if="data.productsPerPage < productsStore.getAllProductsCount">
                    <i class="bi bi-arrow-down"></i> Load More
                </button>
            </div>
    </div>
</template>

<script setup>
    // import the useProductsStore
    import { useProductsStore } from '../../stores/useProductsStore.js'
    import ProductsListItem from './ProductsListItem.vue'
    import Spinner from '../layouts/Spinner.vue'
    import { onMounted, reactive } from 'vue';

    // define the store variable and import the useProductsStore
    const productsStore = useProductsStore()
    
    // defin how many products to show per page
    const data = reactive({
        productsPerPage: productsStore.getProductsPerPage // Local state for products per page
    })

    // call fetchAllProducts when component mounts to fetch the products from the API
    onMounted(() => {
        productsStore.fetchAllProducts()
    })
</script>