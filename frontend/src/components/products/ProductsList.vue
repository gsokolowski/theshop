<template>
    <div class="col-md-8 p-4">
        <div class="row">
            <Spinner :store="productsStore" /> 
            <!-- show found products if there are any -->
            <div class="text-left mb-4" v-if="productsStore.getProductCount > 0">
                <h5>Found {{ productsStore.getProductCount }} products</h5>
            </div>
            <!-- show no products found if there are no products -->
            <div class="text-left mb-4" v-else>
                <h5>No products found</h5>
            </div>
            <ProductsListItem
            v-for="product in productsStore.products"
            :key="product.id"
            :product="product"/>

            <!-- load more products button - server-side pagination -->
            <div class="text-center mt-4">
                    <button
                    name="loadMore"
                    class="btn btn-outline-dark"
                    @click="productsStore.loadMoreProducts()"
                    v-if="productsStore.products.length < productsStore.getProductCount">
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
    import Spinner from '../common/Spinner.vue'
    import { onMounted } from 'vue'

    // console.log('ProductsList component mounted')
    // define the store variable and import the useProductsStore
    const productsStore = useProductsStore()
    
    // call fetchAllProducts when component mounts to fetch the products from the API
    onMounted(() => {
        productsStore.fetchAllProducts()
    })
</script>