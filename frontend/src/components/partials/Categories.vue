<!-- dispalys all categories list in a sidebar -->
<template>
    <h6>Categories</h6>
    <ul class="nav d-flex flex-column">
        <li class="nav-item mb-1" v-for="category in productsStore.categories"
            :key="category.id">
            <button class="btn btn-sm btn-link text-dark"
                @click="productsStore.filterProductsByCategory(category.slug)"
                >
                    {{ category.name }}
            </button>
        </li>
    </ul>
</template>

<script setup>
    // import the useProductsStore
    import { useProductsStore } from '../../stores/useProductsStore.js'

    // define the store variable and import the useProductsStore
    const productsStore = useProductsStore()
</script>

<style scoped>

</style>