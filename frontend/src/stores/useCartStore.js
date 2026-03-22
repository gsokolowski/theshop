import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'
import axios from 'axios'
// define toast
const toast = useToast()

// define the store and name it 'cards'
export const useCartStore = defineStore('cart', {
    state: () => ({ 
        cartItems: [], // cart items array
        isLoading: false, // isLoading state - boolean
        errorMessage: '', // error message string
        validCoupon: {
            coupon_id: null,
            name: null,
            discount: null,
            valid_until: null,
        },
        uniqueHash: null,
    }),
    persist: true, // persist the cart items array
    getters: {
        getCartItems: (state) => state.cartItems,      // Returns cart items array
        getIsLoading: (state) => state.isLoading,      // Returns isLoading state - boolean
        getErrorMessage: (state) => state.errorMessage,      // Returns error message string
    },
    actions: {
        // Transform backend cart item to frontend format
        transformCartItem(backendItem) {
            return {
                id: backendItem.id, // Store backend cart ID for API calls
                reference: backendItem.reference,
                product: backendItem.product,
                qty: backendItem.quantity, // Map quantity to qty
                color: backendItem.color,
                size: backendItem.size,
            }
        },
        // Fetch cart items from backend API
        async fetchCart() {
            this.isLoading = true
            this.errorMessage = ''
            
            try {
                const response = await axios.get('/cart')
                
                if (response.data.status === 200 && response.data.data?.cart_items) {
                    // Transform backend response to frontend format
                    this.cartItems = response.data.data.cart_items.map(item => this.transformCartItem(item))
                    console.log('Cart Items loaded from backend:', this.cartItems)
                } else {
                    this.cartItems = []
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Handle 401 Unauthorized - redirect to login
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to view your cart')
                    return
                }
                
                // Handle other errors
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to load cart items'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error fetching cart:', error)
                throw error
            }
        },
        // add selected by user product with size and color as an item to the cart
        async addToCart(item) {
            this.isLoading = true
            this.errorMessage = ''
            
            // Prepare request data
            const requestData = {
                product_id: item.product.id,
                color_id: item.color.id,
                size_id: item.size.id,
                quantity: item.qty,
            }
            
            console.log('Adding to cart - Request data:', requestData)
            console.log('Item object:', item)
            
            try {
                const response = await axios.post('/cart', requestData)
                
                console.log('Add to cart response:', response.data)
                
                if (response.data.status === 201 || response.data.status === 200) {
                    // Refresh cart from backend to get updated data
                    await this.fetchCart()
                    const message = response.data.message || 'Item added to cart'
                    toast.success(message)
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                console.error('Error adding to cart - Full error:', error)
                console.error('Error response:', error.response)
                console.error('Error response data:', error.response?.data)
                
                // Handle 401 Unauthorized
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to add items to cart')
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
                
                // Handle other errors
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to add item to cart'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error adding to cart:', error)
                throw error
            }
        },
        // increase the quantity of the selected item in the cart
        async increaseQuantity(item) {
            // Check if item has backend ID
            if (!item.id) {
                toast.error('Cart item not found. Please refresh the page.')
                return
            }
            
            // Check maximum quantity before API call
            if (item.qty >= item.product.qty) {
                toast.info("Maximum quantity of " + item.product.qty + " reached")
                return
            }
            
            this.isLoading = true
            this.errorMessage = ''
            
            try {
                const newQuantity = item.qty + 1
                const response = await axios.put(`/cart/${item.id}`, {
                    quantity: newQuantity,
                })
                
                if (response.data.status === 200) {
                    // Refresh cart from backend
                    await this.fetchCart()
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Handle 401 Unauthorized
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to update cart')
                    return
                }
                
                // Handle errors (out of stock, max quantity, etc.)
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to update quantity'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error increasing quantity:', error)
                throw error
            }
        },
        // decrease the quantity of the selected item in the cart
        async decreaseQuantity(item) {
            // Check if item has backend ID
            if (!item.id) {
                toast.error('Cart item not found. Please refresh the page.')
                return
            }
            
            // If quantity is 1, remove the item instead
            if (item.qty <= 1) {
                await this.removeItem(item)
                return
            }
            
            this.isLoading = true
            this.errorMessage = ''
            
            try {
                const newQuantity = item.qty - 1
                const response = await axios.put(`/cart/${item.id}`, {
                    quantity: newQuantity,
                })
                
                if (response.data.status === 200) {
                    // Refresh cart from backend
                    await this.fetchCart()
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Handle 401 Unauthorized
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to update cart')
                    return
                }
                
                // Handle errors
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to update quantity'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error decreasing quantity:', error)
                throw error
            }
        },
        // remove the selected item from the cart
        async removeItem(item) {
            // Check if item has backend ID
            if (!item.id) {
                toast.error('Cart item not found. Please refresh the page.')
                return
            }
            
            this.isLoading = true
            this.errorMessage = ''
            
            try {
                const response = await axios.delete(`/cart/${item.id}`)
                
                if (response.data.status === 200) {
                    // Refresh cart from backend
                    await this.fetchCart()
                    const message = response.data.message || 'Item removed from cart'
                    toast.success(message)
                }
                
                this.isLoading = false
                return response.data
            } catch (error) {
                this.isLoading = false
                
                // Handle 401 Unauthorized
                if (error.response?.status === 401) {
                    window.location.href = '/login'
                    toast.error('Please login to remove items from cart')
                    return
                }
                
                // Handle errors
                const errorMessage = error.response?.data?.error || error.response?.data?.message || 'Failed to remove item'
                this.errorMessage = errorMessage
                toast.error(errorMessage)
                console.error('Error removing item:', error)
                throw error
            }
        },
        // clear the cart from the items in the cartItems array
        clearCart(showToast = true) {
            this.cartItems = [] // override state cartItems array with an empty array
            if (showToast) {
                toast.success("Cart cleared")
            }
            console.log('Cart Items', this.cartItems);
        },
        // set the valid coupon
        setValidCoupon(coupon) {
            this.validCoupon = coupon
        },
        // add the coupon to the cart item
        addCouponToCartItem(coupon_id) {
            this.cartItems = this.cartItems.map(cartItem => {
                return {...cartItem, coupon_id: coupon_id}
            })
            console.log('Cart Items with coupon', this.cartItems);
        },
        // remove the coupon from the cart cartItems array
        removeCouponFromAllItems() {
            this.cartItems = this.cartItems.map(cartItem => {
                return {...cartItem, coupon_id: null}
            })
            // Clear the valid coupon state
            this.validCoupon = {
                coupon_id: null,
                name: null,
                discount: null,
                valid_until: null,
            }
            console.log('Cart Items without coupon', this.cartItems);
        },
        // set the unique hash
        setUniqueHash(hash) {
            this.uniqueHash = hash
        },
    }
  })