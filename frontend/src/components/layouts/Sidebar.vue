<template>
    <!-- Sidebar (1/3) -->
    <aside class="col-md-4 bg-light p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4>Filters</h4>
            <button class="btn btn-link text-dark"
                @click="productsStore.clearFilters">
                Clear all
            </button>
        </div>
        <div class="mb-3">
            <Brands />
            <Categories />
            <Sizes />
            <Colors />
        </div>  
    </aside>
</template>

<script setup>
    import Colors from "../partials/Colors.vue"
    import Brands from "../partials/Brands.vue"
    import Categories from "../partials/Categories.vue"
    import Sizes from "../partials/Sizes.vue"

    import { useProductsStore } from "../../stores/useProductsStore"

    //define the store
    const productsStore = useProductsStore()

</script>

<style scoped>
</style>