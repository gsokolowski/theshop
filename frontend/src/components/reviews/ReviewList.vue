<template>
    <div class="card mb-2 col-md-8 offset-md-1">
        <Spinner :store="productDetailsStore" />
        <div class="card-header bg-white">
            <h5 class="text-center mt-2">
                Reviews ({{ reviews.length }})
            </h5>
        </div>
        <div class="card-body">
            <!-- Empty state -->
            <div v-if="reviews.length === 0" class="text-center text-muted py-4">
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
            
            <!-- Reviews list -->
            <div v-for="review in reviews" :key="review.id" class="border-bottom pb-3 mb-3 review-item">
                <div class="d-flex align-items-start mb-2">
                    <!-- User profile image -->
                    <img 
                        v-if="review.user.profile_image_url" 
                        :src="review.user.profile_image_url" 
                        :alt="review.user.name"
                        class="rounded-circle me-3"
                        style="width: 50px; height: 50px; object-fit: cover;"
                    >
                    <div 
                        v-else
                        class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-secondary text-white"
                        style="width: 50px; height: 50px;"
                    >
                        {{ review.user.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                    
                    <div class="flex-grow-1">
                        <!-- User name and date -->
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="mb-0">{{ review.user.name || 'Anonymous' }}</h6>
                        </div>
                        
                        <!-- Rating stars -->
                        <div class="mb-2">
                            <span 
                                v-for="star in 5" 
                                :key="star"
                                :class="star <= review.rating ? 'bi-star-fill text-warning' : 'bi-star text-muted'"
                                class="bi"
                            ></span>
                            <span class="ms-2 text-muted">({{ review.rating }})</span>
                        </div>
                        
                        <!-- Review title -->
                        <h6 class="mb-1">{{ review.title }}</h6>
                        
                        <!-- Review body -->
                        <p class="mb-0 text-muted">{{ review.body }}</p>
                        <small class="text-muted">{{ review.created_at }}</small>
                    </div>
                    <!-- ✅ CHANGED: Added review-actions class and kept existing conditions -->
                    <div class="d-flex flex-column align-items-center review-actions"
                        v-if="authStore.isUserLoggedIn && authStore.user.id === review.user_id">
                        <button class="btn btn-sm btn-danger mb-2"
                            @click="handleRemoveReview(review)" 
                            >
                            <i class="bi bi-trash"></i>
                        </button>
                        <button class="btn btn-sm btn-warning mb-2"
                            @click="handleEditReview(review)" 
                            >
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
    import { useAuthStore } from '../../stores/useAuthStore'
    import { useToast } from 'vue-toastification' // ✅ ADDED: Import toast
    import { computed } from 'vue' 
    import Spinner from '../common/Spinner.vue'

    const productDetailsStore = useProductDetailsStore()
    const authStore = useAuthStore()
    const toast = useToast() // ✅ ADDED: Initialize toast

    const reviews = computed(() => productDetailsStore.getReviews)
    const user = computed(() => authStore.getUser)

    // Handle remove review with success/error handling
    const handleRemoveReview = async (review) => {
        try {
            const response = await productDetailsStore.removeReview(review)
            // If we reach here, the review was deleted successfully
            if (response.status === 200) {
                toast.success(response.data.message)
            } else {
                toast.error(response.data.error)
            }
        } catch (error) {
            // Error handling
            if (error.response?.data?.error) {
                toast.error(error.response.data.error)
            } else if (error.response?.data?.message) {
                toast.error(error.response.data.message)
            } else {
                toast.error('Failed to delete review')
            }
            console.error('Error deleting review:', error)
        }
    }

    // Handle edit review - set review to update in store
    const handleEditReview = (review) => {
        productDetailsStore.setReviewToUpdate(review)
    }
</script>

<style scoped>
    .border-bottom:last-child {
        border-bottom: none !important;
    }
    
    /* Hide review actions by default */
    .review-item .review-actions {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }
    
    /* Show review actions on hover */
    .review-item:hover .review-actions {
        opacity: 1;
    }
</style>