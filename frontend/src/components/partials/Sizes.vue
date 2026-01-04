<!-- displays all sizes list in a sidebar -->
<template>
    <h6>Sizes</h6>
    <div class="d-flex flex-wrap gap-2">
        <div 
            v-for="size in productsStore.sizes"
            :key="size.id"
            @click="productsStore.filterProductsBySize(size.id)"
            :style="{
                width: '35px',
                height: '35px',
                border: '1px solid #ddd',
                borderRadius: '4px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                cursor: 'pointer',
                fontSize: '14px',
                fontWeight: 'normal'
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