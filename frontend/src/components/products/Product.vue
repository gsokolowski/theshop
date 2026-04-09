<!--Full Product Details Page-->
<template>
    <div class="row">
        <Spinner :store="productDetailsStore" />   
        <div class="text-start mb-2 mt-2">
            <button class="btn" @click="router.push('/')">
                <i class="bi bi-arrow-left"></i> Back
            </button>
        </div>
    </div>
    <div v-if="isLoading">
        <h1>Loading...</h1>
    </div>
    <div v-else-if="product && imagesReady">       
        <div class="row">
            <div class="col-6 mb-3 product-gallery-column">
                <!-- Thumbnail image -->
                <div class="mb-3 rounded product-image-slot">
                    <VueImageZoomer 
                        v-if="imagesReady && product.thumbnail" 
                        :key="product.slug"
                        :regular="product.thumbnail"
                        img-class="img-fluid rounded w-100"
                    />
                    <img 
                        v-else-if="imagesReady && !product.thumbnail"
                        :src="placeholderImage"
                        alt="No Image"
                        class="img-fluid rounded"
                        style="width: 100%; height: auto; background-color: #e0e0e0;"
                    />
                </div>
                <!-- Other images below thumbnail -->
                <div v-if="productImages && productImages.length > 0" class="row g-2 rounded">
                    <div 
                        v-for="productImage in productImages"
                        :key="productImage.id"
                        class="col-6 product-gallery-column"
                    >
                    <div class="product-image-slot">
                    <VueImageZoomer 
                        v-if="productImage.src && imagesReady"
                        img-class="img-fluid rounded w-100"   
                        :regular="productImage.src" 
                        :key="productImage.id"
                    />
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card mb-2" style="max-width: 320px">
                    <div class="card-body">
                        <h5 class="card-title">{{ product.name }}</h5>
                        <p class="card-text">{{ product.description}}</p>
                        <p class="card-text fw-bold">Brand: {{ product.brand.name }}</p>
                        <p class="card-text fw-bold">Category: {{ product.category.name }}</p>
                        <div class="mb-2">
                            <span class="badge bg-success" v-if="productDetailsStore.product?.status">
                                In Stock
                            </span>
                            <span class="badge bg-warning" v-else>
                                Out Stock
                            </span>
                        </div>
                        <div class="mb-2">
                            <span class="h6 mb-0 mt-2">Select Color</span>
                        </div>
                        <div class="d-flex flex-wrap justify-content-start">
                            <div 
                                :class="`${data.chosenColor?.id === color.id ? 'border border-light-subtle shadow-sm border-2 rounded' : ''}  mb-1 me-1`" 
                                v-for="color in productDetailsStore.product?.colors"
                                :key="color.id"
                                :style="{
                                    backgroundColor:color.name,
                                    width:'30px',
                                    height:'30px',
                                    cursor:'pointer'
                                }"
                                @click="setChosenColor(color)"
                            >
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="h6 mb-0">Select Size</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="d-flex flex-wrap justify-content-start align-items-center my-3">
                            <button 
                                :class="`${data.chosenSize?.id === size.id ? 'btn btn-primary mb-3 mx-1 rounded-0' : 'btn btn-sm btn-outline-secondary mb-3 mx-1'}`"
                                v-for="size in productDetailsStore.product?.sizes"
                                :key="size.id"
                                @click="setChosenSize(size)"
                            >
                                {{ size.name }}
                            </button>
                        </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0 mt-2">${{ product.price }}</span>
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
                        <div>
                            <input type="number" 
                                v-model="data.qty" 
                                min="1"
                                :max="product.qty"
                                class="form-control"
                            >
                        </div>
                        <button class="btn btn-danger btn-sm" 
                            @click="handleAddToCart"
                            :disabled="!data.chosenColor || !data.chosenSize || !data.qty "
                        >
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </button>
                        <button 
                            class="btn btn-outline-secondary btn-sm"
                            @click="handleToggleWishlist"
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
        </div>
        <div class="row">                    
            <ReviewList />
            <EditReview ref="editReviewRef" v-if="isEditingReview" />
            <AddReview v-else :rating="data.rating" :max-rating="5" :increment="0.5" />
        </div>
    </div>
</template>

<script setup>
    import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
    import { useCartStore } from '../../stores/useCartStore'
    import { useWishlistStore } from '../../stores/useWishlistStore'
    import { onMounted, computed, watch, reactive, ref, nextTick } from 'vue'
    import { useRoute, useRouter } from 'vue-router'
    import { useToast } from 'vue-toastification'
    import Spinner from '../common/Spinner.vue'
    import AddReview from '../reviews/AddReview.vue'
    import EditReview from '../reviews/EditReview.vue'
    import ReviewList from '../reviews/ReviewList.vue'
    import StarRating from 'vue-star-rating' // Import StarRating component
    
    const route = useRoute() // to get the slug from the route
    const router = useRouter() // to get back to the products list
    const toast = useToast()

    const productDetailsStore = useProductDetailsStore()
    const cartStore = useCartStore()
    const wishlistStore = useWishlistStore()

    // Use computed to make it reactive
    const product = computed(() => productDetailsStore.getProduct)
    const productImages = computed(() => productDetailsStore.getProductImages)
    const isLoading = computed(() => productDetailsStore.getIsLoading)
    
    // Placeholder image as data URI (base64 encoded SVG)
    const placeholderImage = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjYwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZTBlMGUwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzk5OTk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='
    
    // Check if user is editing a review
    const isEditingReview = computed(() => {
        const reviewToUpdate = productDetailsStore.getReviewToUpdate
        return reviewToUpdate.updating === true && reviewToUpdate.data.id !== null
    })

    // Ref for EditReview component
    const editReviewRef = ref(null)

    // Use the getter from store instead of local computed to get the average rating
    const averageRating = computed(() => productDetailsStore.getAverageRating)
    
    // Check if current product is in wishlist
    const isInWishlist = computed(() => {
        if (!product.value?.id) return false
        return wishlistStore.isProductInWishlist(product.value.id)
    })

    // Watch for edit mode and scroll to form
    watch(isEditingReview, async (isEditing) => {
        if (isEditing) {
            await nextTick() // Wait for EditReview component to render
            if (editReviewRef.value?.$el) {
                editReviewRef.value.$el.scrollIntoView({ behavior: 'smooth', block: 'start' })
            }
        }
    })
    // Track when images are ready
    const imagesReady = ref(false)

    // Function to fetch product and track when images are ready
    const fetchProduct = async () => {
        imagesReady.value = false
        productDetailsStore.fetchProduct(route.params.slug)
        
        // ✅ Wait for DOM to update
        await nextTick()
        await nextTick() // Double nextTick to ensure VueImageZoomer can access DOM
        imagesReady.value = true
    }

    onMounted(async () => {
        fetchProduct() // fetch the product from the API on mount
        // Fetch wishlist to check if product is in wishlist
        try {
            await wishlistStore.fetchWishlist()
        } catch {
            // Silently fail - user might not be logged in
        }
    })

    // Watches route.params.slug for changes. When the slug changes, it calls fetchProduct() to load the new product.
    watch(() => route.params.slug, async (newSlug) => {
        if (newSlug) {
            await fetchProduct()
        }
    })

    //define the data object
    const data = reactive({
        qty: 1,
        chosenColor: null,
        chosenSize: null
    })

    //set the chosen color by user
    const setChosenColor = (color) => {
        data.chosenColor = color
        // console.log('Data', data)
    }

    //set the chosen size by user
    const setChosenSize = (size) => {
        data.chosenSize = size
        // console.log('Data', data)
    }

    // Button calls Add to cart handler when clicked
    const handleAddToCart = () => {
        // Check if product is out of stock
        if (!productDetailsStore.product?.status) {
            toast.warning("Product is out of stock")
            return
        }        
        // item to add to the cart
        const item = { // you can do verification here
            // crate unique reference for the item
            reference: `${productDetailsStore.getProduct.id}-${data.chosenColor.id}-${data.chosenSize.id}`,
            product: productDetailsStore.getProduct, // or product.value passing the whole product object
            qty: data.qty, // quantity
            color: data.chosenColor, // color object
            size: data.chosenSize // size object
        }

        // send the item to the cartStore.addToCart(item)
        cartStore.addToCart(item)
    }
    
    // Handle wishlist toggle
    const handleToggleWishlist = async () => {
        if (!product.value?.id) return
        
        try {
            await wishlistStore.toggleWishlist(product.value.id)
        } catch (error) {
            console.error('Error toggling wishlist:', error)
        }
    }
    
</script>

<style scoped>
/* Flex row children default to min-width:auto; wide images would expand col-6 past layout */
.product-gallery-column {
    min-width: 0;
}
/* vue-image-zoomer wraps the img; keep media inside column bounds */
.product-image-slot {
    max-width: 100%;
}
.product-gallery-column :deep(img) {
    max-width: 100%;
    height: auto;
}
</style>