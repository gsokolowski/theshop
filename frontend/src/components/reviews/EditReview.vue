<template>
    <div class="card mb-2 col-md-8 offset-md-1">
        <Spinner :store="productDetailsStore" />
        <div class="card-header bg-white">
            <h5 class="text-center mt-2">
                Edit your review
            </h5>
        </div>
        <div class="card-body">
            <form @submit.prevent="handleUpdateReview">
                <div class="mb-3">
                    <label for="edit-title" class="form-label">Title *</label>
                    <input 
                    type="text" 
                    class="form-control" 
                    id="edit-title" 
                    v-model="data.title"
                    placeholder="title"
                    >
                </div>
                <div class="mb-3">
                    <label for="edit-body" class="form-label">Body *</label>
                    <textarea 
                        class="form-control" 
                        id="edit-body" 
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
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Update Review</button>
                    <button class="btn btn-secondary" type="button" @click="handleCancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
    import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
    import { useToast } from 'vue-toastification'
    import { useRouter } from 'vue-router'
    import Spinner from '../common/Spinner.vue'
    import StarRating from 'vue-star-rating'
    import { reactive, computed, watch } from 'vue'

    const productDetailsStore = useProductDetailsStore()
    const toast = useToast()
    const router = useRouter()

    const reviewToUpdate = computed(() => productDetailsStore.getReviewToUpdate)

    // Initialize form data from store
    const data = reactive({
        title: '',
        body: '',
        rating: 0,
    })

    // Watch for changes in reviewToUpdate and update form data
    watch(reviewToUpdate, (newValue) => {
        if (newValue.updating && newValue.data.id) {
            data.title = newValue.data.title || ''
            data.body = newValue.data.body || ''
            data.rating = newValue.data.rating || 0
        }
    }, { immediate: true, deep: true })

    // Handle cancel - clear reviewToUpdate state
    const handleCancel = () => {
        productDetailsStore.clearReviewToUpdate()
    }

    // Handle update review
    const handleUpdateReview = async () => {
        try {
            // Validation check before sending
            if (!reviewToUpdate.value.data.id) {
                toast.error('Review not found')
                return
            }
            if (!data.title.trim() || !data.body.trim() || data.rating === 0) {
                toast.error('Please fill in all fields and select a rating')
                return
            }
            
            // Call store method to update review
            const response = await productDetailsStore.updateReview({
                id: reviewToUpdate.value.data.id,
                title: data.title,
                body: data.body,
                rating: data.rating,
            })
            
            // Display success message from backend response
            toast.success(response.data.message)

            
            // Form will be cleared automatically by clearReviewToUpdate() in store
        } catch (error) {
            if (error.response?.status === 401) {
                toast.error('Please login to update a review')
                router.push('/login')
            } else if (error.response?.data?.error) {
                toast.error(error.response.data.error)
            } else if (error.response?.data?.message) {
                toast.error(error.response.data.message)
            } else {
                toast.error('Failed to update review')
            }
            console.error('Error:', error)
        }
    }
</script>

<style scoped>

</style>
