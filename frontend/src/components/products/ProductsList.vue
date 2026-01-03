<template>
    <div class="col-md-8 p-4">
        <div class="row">
            <Spinner :store="productsStore" /> 

            <!-- ✅ Use store's productsPerPage directly -->
            <ProductsListItem 
            v-for="product in productsStore.products.slice(0, productsStore.productsPerPage)" 
            :key="product.id" 
            :product="product"/> <!-- pass the product propsto the ProductsListItem component -->

            <!-- load more products button -->
            <div class="text-center mt-4">
                <!-- if no more products to load hide the button -->
                <!-- ✅ Use store's productsPerPage directly -->
                    <button 
                    name="loadMore" 
                    class="btn btn-outline-dark" 
                    @click="productsStore.loadMoreProducts()" 
                    v-if="productsStore.productsPerPage < productsStore.getProductCount">
                    <i class="bi bi-arrow-down"></i> Load More
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
    // import the useProductsStore
    import { useProductsStore } from '../../stores/useProductsStore.js'
    import ProductsListItem from './ProductsListItem.vue'
    import Spinner from '../layouts/Spinner.vue'
    import { onMounted } from 'vue'

    console.log('ProductsList component mounted')
    // define the store variable and import the useProductsStore
    const productsStore = useProductsStore()
    
    // call fetchAllProducts when component mounts to fetch the products from the API
    onMounted(() => {
        productsStore.fetchAllProducts()
    })
</script>