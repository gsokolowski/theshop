import { defineStore } from 'pinia'
import axios from 'axios'

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
    }),
    getters: {
        getProduct: (state) => state.product,      // Returns product array
        getProductThumbnail: (state) => state.productThumbnail,      // Returns product thumbnail string
        getProductImages: (state) => state.productImages,      // Returns product images array
        getErrorMessage: (state) => state.errorMessage,      // Returns error message string
        getIsLoading: (state) => state.isLoading,      // Returns isLoading state - boolean
    },
    actions: {
        async fetchProduct(slug) {
            console.log('Fetching product:', slug)
            this.isLoading = true
            
            // ✅ Reset state before fetching new product
            this.product = {}
            this.productThumbnail = ''
            this.productImages = []
            this.errorMessage = ''
            
            try {
                const response = await axios.get(`/api/products/${slug}`)
                this.product = response.data.data
                this.productThumbnail = response.data.data.thumbnail || ''
                
                // ✅ Reset and populate images array
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
        }
    }
  })