<template>
    <div class="card mb-2 col-md-8 offset-md-1">
        <Spinner :store="productDetailsStore" />
        <div class="card-header bg-white">
            <h5 class="text-center mt-2">
                Add your review
            </h5>
        </div>
        <div class="card-body">
            <!-- Show message if user hasn't purchased the product -->
            <div v-if="!hasPurchasedProduct" class="text-center text-muted py-4">
                <p class="mb-0">You must purchase this product before you can leave a review.</p>
            </div>
            
            <!-- Display message if user already has a review -->
            <div v-else-if="hasExistingReview" class="text-center text-success py-4">
                <p class="mb-0">Your already have a review for this product.</p>
            </div>

            <!-- Show form if user has purchased -->
            <form v-else @submit.prevent="handleAddReview">
                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input 
                    type="text" 
                    class="form-control" 
                    id="title" 
                    v-model="data.title"
                    placeholder="title"
                    >
                </div>
                <div class="mb-3">
                    <label for="body" class="form-label">Body *</label>
                    <textarea 
                        class="form-control" 
                        id="body" 
                        v-model="data.body"
                        placeholder="body"
                        rows="3"
                    ></textarea>
                </div>
                <div class="mb-3">
                    <StarRating 
                        v-model:rating="data.rating" 
                        :increment="0.5"
                        :max-rating="5"
                        :show-rating="false"
                        :star-size="40"
                    />
                </div>
                <button class="btn btn-primary" type="submit">Add Review</button>        
            </form>
        </div>
    </div>
</template>

<script setup>
    import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
    import { useAuthStore } from '../../stores/useAuthStore'
    import { useToast } from 'vue-toastification'
    import { useRouter } from 'vue-router'
    import Spinner from '../common/Spinner.vue'
    import StarRating from 'vue-star-rating'
    import {reactive, computed, ref, onMounted, watch} from 'vue'
    import axios from 'axios'

    const productDetailsStore = useProductDetailsStore()
    const authStore = useAuthStore()

    const toast = useToast()
    const router = useRouter() 

    const product = computed(() => productDetailsStore.getProduct)

    // Track if user already has a review
    const hasExistingReview = ref(false)

    // Check if user has purchased the product
    const hasPurchasedProduct = computed(() => {
        // If user is not logged in, they can't have purchased anything
        if (!authStore.isUserLoggedIn || !authStore.user) {
            return false
        }
        
        // If user has no orders, they haven't purchased the product
        if (!authStore.user.orders || authStore.user.orders.length === 0) {
            return false
        }
        
        // Check if any order contains the current product
        const productId = product.value?.id
        if (!productId) {
            return false
        }
        
        // Check all orders to see if any contains this product
        return authStore.user.orders.some(order => 
            order.products && order.products.some(p => p.id === productId)
        )
    })

    // ✅ CHANGED: Check if user already has a review for this product (approved or unapproved)
    const checkExistingReview = async () => {
        if (!authStore.isUserLoggedIn || !authStore.user || !product.value?.id) {
            hasExistingReview.value = false
            return
        }
        
        try {
            // ✅ CHANGED: Make proper API call to check if user has a review
            const response = await axios.get(`/api/reviews/check/${product.value.id}`)
            
            if (response.data?.data?.has_review) {
                hasExistingReview.value = true
            } else {
                hasExistingReview.value = false
            }
        } catch (error) {
            // If error, assume no review exists
            hasExistingReview.value = false
            console.error('Error checking review:', error)
        }
    }

    // ✅ ADDED: Check on mount and watch for product changes
    onMounted(() => {
        checkExistingReview()
    })

    // ✅ ADDED: Watch product to re-check when product changes
    watch(() => product.value?.reviews, () => {
        checkExistingReview()
    }, { deep: true })

    // define the data object
    const data = reactive({
        title: '',
        body: '',
        rating: 0,
    })
    
    // handle add review to be able to display errors in Component not from store
    const handleAddReview = async () => {
        try {
            // Validation check before sending
            if (!product.value?.id) {
                toast.error('Product not found')
                return
            }
            // Check if user has purchased the product before submitting
            if (!hasPurchasedProduct.value) {
                toast.error('You must purchase this product before you can leave a review')
                return
            }  
            // Validation check for title, body, and rating          
            if (!data.title.trim() || !data.body.trim() || data.rating === 0) {
                toast.error('Please fill in all fields and select a rating')
                return
            }
            // ✅ ADDED: Check if user already has a review
            if (hasExistingReview.value) {
                toast.error('You have already submitted a review for this product')
                return
            }            
            // Call store method with product_id included
            const response = await productDetailsStore.addReview({
                title: data.title,
                body: data.body,
                rating: data.rating,
                product_id: product.value.id,
            })
            
            // Display success message from backend response
            toast.success(response.data.message || 'Review submitted successfully!')

            // Set flag after successful submission
            hasExistingReview.value = true            

            // Clear form
            data.title = ''
            data.body = ''
            data.rating = 0
        } catch (error) {
            if (error.response?.status === 401) {
                toast.error('Please login to submit a review')
                router.push('/login')
            } else if (error.response?.data?.error) {
                toast.error(error.response.data.error)
            } else if (error.response?.data?.message) {
                toast.error(error.response.data.message)
            } else {
                toast.error('Failed to submit review')
            }
            console.error('Error:', error)
        }
    }
</script>

<style scoped>

</style>