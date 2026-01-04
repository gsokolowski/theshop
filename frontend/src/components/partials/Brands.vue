<!-- dispalys all brands list in a sidebar -->
<template>
    <h6 class="mt-3 mb-3">Brands</h6>
    <ul class="nav d-flex flex-column">
        <li class="nav-item mb-1" v-for="brand in productsStore.brands"
            :key="brand.id">
            <button class="btn btn-sm btn-link text-dark"
                @click="productsStore.filterProductsByBrand(brand.slug)"
                >
                    {{ brand.name }}
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