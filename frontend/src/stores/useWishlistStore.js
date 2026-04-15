import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { redirectToLogin } from '../utils/authRedirect.js'

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
                const response = await axios.get('/wishlist')
                
                if (response.data.status === 200 && response.data.data?.wishlist_items) {
                    this.wishlistItems = response.data.data.wishlist_items
                    // console.log('Wishlist items loaded from backend:', this.wishlistItems)
                } else {
                    this.wishlistItems = []
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Guest / no session: empty list (no redirect — used when browsing catalog)
                if (error.response?.status === 401) {
                    this.wishlistItems = []
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
                const response = await axios.post('/wishlist', {
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
                
                if (error.response?.status === 401) {
                    toast.error('Please login or register to add items to your wishlist')
                    redirectToLogin()
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
                const response = await axios.delete(`/wishlist/${wishlistId}`)
                
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
                
                if (error.response?.status === 401) {
                    toast.error('Please login or register to manage your wishlist')
                    redirectToLogin()
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
