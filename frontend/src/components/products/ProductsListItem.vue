<template>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<div class="col-md-6">
    <div class="card mb-2" style="max-width: 320px">
        <img :src="product.thumbnail" class="card-img-top" alt="Product Image">
        <div class="card-body">
            <router-link :to="{ name: 'product', params: { slug: product.slug } }">
                <h5 class="card-title">{{ product.name }}</h5>
            </router-link>
            <p class="card-text">{{ product.description.substring(0,50) }}</p>
            <p class="card-text">Brand: {{ product.brand.name }}</p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="h5 mb-0">${{ product.price }}</span>
                <div v-if="product.reviews.length > 0" class="d-flex align-items-center"> 
                    <StarRating 
                    :rating="Number(averageRating)"
                    :increment="0.5"
                    :max-rating="5"
                    :show-rating="false"
                    :star-size="20"
                    :read-only="true"
                />
                    <small class="text-muted ms-2 mt-2">({{ product.reviews.length }})</small>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between bg-light">
            <button class="btn btn-danger btn-sm"><i class="bi bi-cart-plus"></i> Add to Cart</button>
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-heart"></i></button>
        </div>
    </div>
</div>
</template>

<script setup>
import StarRating from 'vue-star-rating' // Import StarRating component
import { useProductsStore } from '../../stores/useProductsStore.js'
import { computed } from 'vue'
const productsStore = useProductsStore()

// Use the getter from store instead of local computed to get the average rating
// Calculate average rating for this specific product
const averageRating = computed(() => {
    const reviews = props.product?.reviews || []
    if (reviews.length === 0) return 0
    const totalRating = reviews.reduce((sum, review) => sum + Number(review.rating), 0)
    const average = totalRating / reviews.length
    return Math.round(average * 2) / 2 // round to nearest 0.5
})

// define the props for the component  it is passed from the ProductsList component
const props = defineProps({
    product: {
        type: Object,        // Prop must be an Object
        required: true       // Prop is required (must be passed)
    }
})

console.log(props.product) // log the product to the console

</script>

<style scoped>
.product-list-item {
    border: 1px solid #ccc;
    padding: 10px;
    margin: 10px;
    border-radius: 5px;
}
</style>