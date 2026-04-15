<template>
    <!-- Sidebar (1/3) -->
    <aside class="col-md-4 bg-light p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4>Filters</h4>
            <!-- ✅ CHANGED: match facet active styling when any filter or search is set -->
            <button
                type="button"
                class="btn btn-link"
                :class="hasActiveFilters ? 'text-primary fw-semibold' : 'text-dark'"
                @click="productsStore.clearFilters"
            >
                Clear all
            </button>
        </div>
        <div class="mb-3">
            <SearchForm />
            <Categories />
            <Brands />
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
    import SearchForm from "../partials/SearchForm.vue"

    import { computed } from 'vue'
    import { useProductsStore } from "../../stores/useProductsStore"

    const productsStore = useProductsStore()

    const hasActiveFilters = computed(() => {
        const f = productsStore.filters
        if (f.categorySlug || f.brandSlug || f.colorId != null || f.sizeId != null) {
            return true
        }
        return (productsStore.searchTerm || '').trim().length > 0
    })

</script>

<style scoped>
</style>