import { defineStore } from 'pinia'
import axios from 'axios'
import { useToast } from 'vue-toastification'
// define toast
const toast = useToast()
// define the store and name it 'products'
export const useProductDetailsStore = defineStore('product', {
    state: () => ({ 
        // state holds the initial values for your store's data: products, categories, brands, colors, sizes, isLoading, filter
        // state creates a new state object for each store instance - each instance has its own copy of the state
        product: [], // product array
        productThumbnail: '', // product thumbnail string
        productImages: [], // product images array
        isLoading: false, // isLoading state - boolean
        errorMessage: '', // error message string
        reviewsToUpdate: {
            updating: false,
            data: null, // review data to update
        }
    }),
    getters: {
        getProduct: (state) => state.product,      // Returns product array
        getProductThumbnail: (state) => state.productThumbnail,      // Returns product thumbnail string
        getProductImages: (state) => state.productImages,      // Returns product images array
        getErrorMessage: (state) => state.errorMessage,      // Returns error message string
        getIsLoading: (state) => state.isLoading,      // Returns isLoading state - boolean
        getReviews: (state) => state.product?.reviews || [],      // Returns reviews array
    },
    actions: {
        async fetchProduct(slug) {
            console.log('Fetching product:', slug)
            this.isLoading = true
            
            // Reset all state before fetching new product
            this.product = {}
            this.productThumbnail = ''
            this.productImages = []
            this.errorMessage = ''

            try {
                const response = await axios.get(`/api/products/${slug}`)
                this.product = response.data.data
                this.productThumbnail = response.data.data.thumbnail || ''

                // Reset and populate images array
                this.productImages = []
                if (response.data.data.first_image) {
                    this.productImages.push({
                        id: 1,
                        src: response.data.data.first_image,
                    })
                }
                if (response.data.data.second_image) {
                    this.productImages.push({
                        id: 2,
                        src: response.data.data.second_image,
                    })
                }
                if (response.data.data.third_image) {
                    this.productImages.push({
                        id: 3,
                        src: response.data.data.third_image,
                    })
                }
            } catch (error) {
                console.error('Error fetching product:', error)
                this.errorMessage = error.response?.data?.message || 'Error fetching product'
            } finally {
                this.isLoading = false
            }
        },
        async addReview(reviewData) {
            try {
                // Make API call to create review
                const response = await axios.post('/api/reviews', {
                    title: reviewData.title,
                    body: reviewData.body,
                    rating: reviewData.rating,
                    product_id: reviewData.product_id, // ✅ Get product_id from parameter
                })
                
                return response // Return response so component can handle success
            } catch (error) {
                
                this.errorMessage = error.response?.data?.error || 'Failed to add review'
                throw error // Re-throw so component can handle it
            }
        },
        async removeReview(review) {
            try {
                // Make API call to delete review
                await axios.delete(`/api/reviews/${review.id}`)
                
                // Only update local state if API call succeeds
                if (this.product?.reviews) {
                    this.product.reviews = this.product.reviews.filter(r => r.id !== review.id)
                }
                toast.success('Review removed successfully!')
            } catch (error) {
                console.error('Error removing review:', error)
                this.errorMessage = error.response?.data?.error || 'Failed to remove review'
                throw error // Re-throw so component can handle it
            }
        },
        async editReview(review) {
            try {
                // Make API call to update review
                const response = await axios.put(`/api/reviews/${review.id}`, {
                    title: review.title,
                    body: review.body,
                    rating: review.rating,
                })
                
                // Update local state with the response from API (ensures we have latest data)
                if (this.product?.reviews && response.data?.data?.review) {
                    this.product.reviews = this.product.reviews.map(r => 
                        r.id === review.id ? response.data.data.review : r
                    )
                }
            } catch (error) {
                console.error('Error updating review:', error)
                this.errorMessage = error.response?.data?.error || 'Failed to update review'
                throw error // Re-throw so component can handle it
            }
        }
    }
  })