<!-- displays all sizes list in a sidebar -->
<template>
    <h6 class="mt-3 mb-3">Sizes</h6>
    <div class="d-flex flex-wrap gap-2">
        <!-- ✅ CHANGED: emphasize selected size when it is in the active filter set -->
        <div 
            v-for="size in productsStore.sizes"
            :key="size.id"
            @click="productsStore.filterProductsBySize(size.id)"
            :style="{
                width: '35px',
                height: '35px',
                border: productsStore.filters.sizeId === size.id ? '2px solid var(--bs-primary, #0d6efd)' : '1px solid #ddd',
                borderRadius: '4px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                cursor: 'pointer',
                fontSize: '14px',
                fontWeight: productsStore.filters.sizeId === size.id ? '600' : 'normal'
            }">
            {{ size.name }}
        </div>
    </div>
</template>

<script setup>
    import { useProductsStore } from '../../stores/useProductsStore.js'

    const productsStore = useProductsStore()
</script>

<style scoped>

</style>