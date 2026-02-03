<template>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
<div class="col-md-6">
    <div class="card mb-2" style="max-width: 320px">
        <img 
            :src="product.thumbnail || placeholderImage" 
            class="card-img-top" 
            alt="Product Image"
            style="object-fit: cover; height: 200px; background-color: #e0e0e0;"
        >
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
            <button 
                class="btn btn-outline-secondary btn-sm"
                @click.stop="handleToggleWishlist"
                :disabled="wishlistStore.isLoading"
            >
                <i 
                    :class="isInWishlist ? 'bi bi-heart-fill' : 'bi bi-heart'"
                    :style="isInWishlist ? { color: 'red' } : {}"
                ></i>
            </button>
        </div>
    </div>
</div>
</template>

<script setup>
import StarRating from 'vue-star-rating' // Import StarRating component
import { useProductsStore } from '../../stores/useProductsStore.js'
import { useWishlistStore } from '../../stores/useWishlistStore.js'
import { computed, onMounted } from 'vue'

const productsStore = useProductsStore()
const wishlistStore = useWishlistStore()

// Use the getter from store instead of local computed to get the average rating
// Calculate average rating for this specific product
const averageRating = computed(() => {
    const reviews = props.product?.reviews || []
    if (reviews.length === 0) return 0
    const totalRating = reviews.reduce((sum, review) => sum + Number(review.rating), 0)
    const average = totalRating / reviews.length
    return Math.round(average * 2) / 2 // round to nearest 0.5
})

// Check if product is in wishlist
const isInWishlist = computed(() => {
    if (!props.product?.id) return false
    return wishlistStore.isProductInWishlist(props.product.id)
})

// define the props for the component  it is passed from the ProductsList component
const props = defineProps({
    product: {
        type: Object,        // Prop must be an Object
        required: true       // Prop is required (must be passed)
    }
})

// Handle wishlist toggle
const handleToggleWishlist = async () => {
    if (!props.product?.id) return
    
    try {
        await wishlistStore.toggleWishlist(props.product.id)
    } catch (error) {
        console.error('Error toggling wishlist:', error)
    }
}

// Fetch wishlist on mount to check if products are in wishlist
onMounted(async () => {
    try {
        await wishlistStore.fetchWishlist()
    } catch (error) {
        // Silently fail - user might not be logged in
        console.log('Could not fetch wishlist:', error)
    }
})

console.log(props.product) // log the product to the console

// Placeholder image as data URI (base64 encoded SVG) - smaller size for list items
const placeholderImage = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZTBlMGUwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5OTk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='

</script>

<style scoped>
.product-list-item {
    border: 1px solid #ccc;
    padding: 10px;
    margin: 10px;
    border-radius: 5px;
}
</style>