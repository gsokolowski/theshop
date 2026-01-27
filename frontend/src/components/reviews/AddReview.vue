<template>
    <div class="card mb-2 col-md-8 offset-md-1">
        <Spinner :store="productDetailsStore" />
        <div class="card-header bg-white">
            <h5 class="text-center mt-2">
                Add your review
            </h5>
        </div>
        <div class="card-body">
            <form @submit.prevent="handleAddReview">
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
    import { useToast } from 'vue-toastification'
    import { useRouter } from 'vue-router'
    import Spinner from '../common/Spinner.vue'
    import StarRating from 'vue-star-rating'
    import {reactive, computed} from 'vue'
    

    const productDetailsStore = useProductDetailsStore()

    const toast = useToast()
    const router = useRouter() 

    const product = computed(() => productDetailsStore.getProduct)

    // define the data object
    const data = reactive({
        title: '',
        body: '',
        rating: 0,
        
    })
    
    const handleAddReview = async () => {
        try {
            // Validation check before sending
            if (!product.value?.id) {
                toast.error('Product not found')
                return
            }
            if (!data.title.trim() || !data.body.trim() || data.rating === 0) {
                toast.error('Please fill in all fields and select a rating')
                return
            }
            
            // Call store method with product_id included
            await productDetailsStore.addReview({
                title: data.title,
                body: data.body,
                rating: data.rating,
                product_id: product.value.id, // Set dynamically from computed product
            })
            
            toast.success('Review submitted successfully!')
            
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