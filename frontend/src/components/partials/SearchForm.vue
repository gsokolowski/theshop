<template>
    <h6 class="mt-3 mb-3">Search Products</h6>
    <form @submit.prevent="productsStore.filterProductsBySearchTerm" class="d-flex align-items-center">
        <input type="text" v-model="productsStore.searchTerm" placeholder="search">
        <button class="btn btn-sm btn-dark search__button"
            type="submit">
            <i class="bi bi-search"></i>
        </button>
    </form>
</template>

<script setup>
    import { useProductsStore } from '../../stores/useProductsStore'
    const productsStore = useProductsStore()
</script>

<style scoped>
    .search__button{
        border-top-right-radius: 15px !important;
        border-bottom-right-radius: 15px !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }
</style>