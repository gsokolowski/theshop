<template>
    <div class="mb-3">
        <h5>Add Review</h5>
        <form @submit.prevent="handleAddReview">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input 
                type="text" 
                class="form-control" 
                id="title" 
                v-model="data.title"
                placeholder="Enter title"
                >
            </div>
            <div class="mb-3">
                <label for="body" class="form-label">Body</label>
                <textarea 
                    class="form-control" 
                    id="body" 
                    v-model="data.body"
                    placeholder="Enter body"
                    rows="3"
                ></textarea>
            </div>
            <div class="mb-3">
                <StarRating 
                    v-model:rating="data.rating" 
                    :increment="0.5"
                    :max-rating="5"
                    :show-rating="false"
                />
            </div>
            <button class="btn btn-primary" type="submit">Add Review</button>        
        </form>
    </div>
</template>

<script setup>
    import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
    import { useToast } from 'vue-toastification'
    import { useRoute } from 'vue-router'
    import StarRating from 'vue-star-rating'
    import { onMounted, reactive, computed} from 'vue'
    import axios from 'axios'

    const productDetailsStore = useProductDetailsStore()

    const toast = useToast()
    const route = useRoute()

    const product = computed(() => productDetailsStore.getProduct)
    console.log('Product ID', product.value.id)

    // define the data object
    const data = reactive({
        title: '',
        body: '',
        rating: 0,
    })
    
    // handle add review
    const handleAddReview = async () => {
        try {

            // Access product.value?.id directly (with optional chaining for safety)
            if (!product.value?.id) {
                toast.error('Product not found')
                return
            }
            // Validation check before sending
            if (!data.title.trim() || !data.body.trim() || data.rating === 0) {
                toast.error('Please fill in all fields and select a rating')
                return
            }
            const response = await axios.post('/api/reviews', {
                title: data.title,
                body: data.body,
                rating: data.rating,
                product_id: product.value.id, // ✅ Use product.value.id directly
            })

            if (response.status === 201) {
                toast.success('Review submitted successfully!')
            }
            console.log('Post review response', response)

            // Clear form
            data.title = ''
            data.body = ''
            data.rating = 0
        } catch (error) {
            toast.error('Failed to submit review')
            console.error('Error:', error)
        }
    }

</script>

<style scoped>

</style>