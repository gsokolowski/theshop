import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'
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
        // add selected by user product with size and color as an item to the cart
        addToCart(item) {
            // check if the item already exists in the cartItems array
            const existingItem = this.cartItems.find(cartItem => cartItem.product.id === item.product.id && cartItem.color.id === item.color.id && cartItem.size.id === item.size.id)
            if(existingItem) {
                toast.error("Item already in cart")
                return
            }
            // add the item to the cart
            this.cartItems.push(item)

            toast.success("Item added to cart")
            console.log('Cart Items', this.cartItems);
            
        },
        // increase the quantity of the selected item in the cart
        increaseQuantity(item) {
            console.log('Maximum Quentity', item.product.qty);
            console.log('Current Quentity', item.qty);
            if(item.qty >= item.product.qty) {
                toast.info("Maximum quantity of " + item.product.qty + " reached")
                return
            }
            item.qty++
            console.log('Cart Items', this.cartItems);
        },
        // decrease the quantity of the selected item in the cart
        decreaseQuantity(item) {
            console.log('Current Quentity', item.qty);
            if(item.qty <= 1) {
                toast.info("Minimum quantity reached")
                this.removeItem(item)
                return
            }
            item.qty--
            console.log('Cart Items', this.cartItems);
        },
        // remove the selected item from the cart
        removeItem(item) {
            // remove passed item from cartItems array - overwrite cartItems array with the new array
            this.cartItems = this.cartItems.filter(cartItem => cartItem.reference !== item.reference)
            
            toast.success("Item removed from cart")
            console.log('Cart Items', this.cartItems);
        },
        // clear the cart from the items in the cartItems array
        clearCart() {
            this.cartItems = [] // override state cartItems array with an empty array
            toast.success("Cart cleared")
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