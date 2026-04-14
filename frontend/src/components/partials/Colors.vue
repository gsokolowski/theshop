<!-- dispalys all colors list in a sidebar -->
<template>
  <div class="mb-3 mt-3">
    <h6>Colors</h6>
    <div class="d-flex flex-wrap justify-content-start">
        <!-- ✅ CHANGED: primary border when this color is part of the active filter set -->
        <div
          class="border shadow-sm border-2 rounded mb-1 me-1"
          :class="productsStore.filters.colorId === color.id ? 'border-primary' : 'border-light-subtle'"
          v-for="color in productsStore.colors"
          :key="color.id"
          @click="productsStore.filterProductsByColor(color.id)"
          :style="{
            backgroundColor:color.name,
            width:'30px',
            height:'30px',
            cursor:'pointer'
          }"
          >
        </div>
    </div>
  </div>
</template>

<script setup>
    // import the useProductsStore
    import { useProductsStore } from '../../stores/useProductsStore.js'

    // define the store variable and import the useProductsStore
    const productsStore = useProductsStore()
</script>

<style scoped>

</style>