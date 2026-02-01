import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'
import axios from 'axios'

const toast = useToast()

export const useWishlistStore = defineStore('wishlist', {
    state: () => ({
        wishlistItems: [],
        isLoading: false,
        errorMessage: '',
    }),
    getters: {
        getWishlistItems: (state) => state.wishlistItems,
        getIsLoading: (state) => state.isLoading,
        getErrorMessage: (state) => state.errorMessage,
        isProductInWishlist: (state) => (productId) => {
            return state.wishlistItems.some(item => item.product.id === productId)
        },
    },
    actions: {
        // Fetch all wishlist items from backend API
        async fetchWishlist() {
            this.isLoading = true
            this.errorMessage = ''
            
            try {
                const response = await axios.get('/api/wishlist')
                
                if (response.data.status === 200 && response.data.data?.wishlist_items) {
                    this.wishlistItems = response.data.data.wishlist_items
                    console.log('Wishlist items loaded from backend:', this.wishlistItems)
                } else {
                    this.wishlistItems = []
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Handle 401 Unauthorized - redirect to login
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to view your wishlist')
                    return
                }
                
                // Handle other errors
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to load wishlist items'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error fetching wishlist:', error)
                throw error
            }
        },
        
        // Add product to wishlist
        async addToWishlist(productId) {
            this.isLoading = true
            this.errorMessage = ''
            
            try {
                const response = await axios.post('/api/wishlist', {
                    product_id: productId,
                })
                
                if (response.data.status === 201 || response.data.status === 200) {
                    // Refresh wishlist from backend to get updated data
                    await this.fetchWishlist()
                    const message = response.data.message || 'Product added to wishlist'
                    toast.success(message)
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Handle 401 Unauthorized
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to add items to wishlist')
                    return
                }
                
                // Handle validation errors (422)
                if (error.response?.status === 422) {
                    const validationErrors = error.response?.data?.errors || {}
                    const errorMessage = error.response?.data?.message || 'Validation error'
                    console.error('Validation errors:', validationErrors)
                    this.errorMessage = errorMessage
                    toast.error(errorMessage)
                    throw error
                }
                
                // Handle duplicate product (400)
                if (error.response?.status === 400) {
                    const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Product is already in your wishlist'
                    this.errorMessage = errorMessage
                    toast.info(errorMessage)
                    return
                }
                
                // Handle other errors
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to add item to wishlist'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error adding to wishlist:', error)
                throw error
            }
        },
        
        // Remove product from wishlist
        async removeFromWishlist(wishlistId) {
            this.isLoading = true
            this.errorMessage = ''
            
            try {
                const response = await axios.delete(`/api/wishlist/${wishlistId}`)
                
                if (response.data.status === 200) {
                    // Refresh wishlist from backend
                    await this.fetchWishlist()
                    const message = response.data.message || 'Product removed from wishlist'
                    toast.success(message)
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Handle 401 Unauthorized
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to remove items from wishlist')
                    return
                }
                
                // Handle errors
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to remove wishlist item'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error removing from wishlist:', error)
                throw error
            }
        },
        
        // Toggle wishlist - add if not in wishlist, remove if already in wishlist
        async toggleWishlist(productId) {
            // Check if product is already in wishlist
            const existingItem = this.wishlistItems.find(item => item.product.id === productId)
            
            if (existingItem) {
                // Remove from wishlist
                await this.removeFromWishlist(existingItem.id)
            } else {
                // Add to wishlist
                await this.addToWishlist(productId)
            }
        },
    }
})
