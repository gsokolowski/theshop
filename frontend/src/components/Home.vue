<template>
    <div class="row min-vh-100">
        <Sidebar />
        <ProductsList />
    </div>
</template>

<script setup>
    import Sidebar from './layouts/Sidebar.vue'
    import ProductsList from './products/ProductsList.vue'
    import { useProductsStore } from '../stores/useProductsStore'
    import { onMounted } from 'vue'

    const productsStore = useProductsStore()

    onMounted(() => {
        // clear filters when the component mounts when user clicks on the shop navbar link
        productsStore.clearFilters()
        console.log('Filters cleared')
    })
</script>