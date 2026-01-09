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
    }),
    persist: true, // persist the cart items array
    getters: {
        getErrorMessage: (state) => state.errorMessage,      // Returns error message string
        getIsLoading: (state) => state.isLoading,      // Returns isLoading state - boolean
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
                toast.error("Maximum quantity reached")
                return
            }
            item.qty++
            toast.success("Quantity increased")
            console.log('Cart Items', this.cartItems);
        },
        // decrease the quantity of the selected item in the cart
        decreaseQuantity(item) {
            console.log('Current Quentity', item.qty);
            if(item.qty <= 1) {
                toast.error("Minimum quantity reached")
                return
            }
            item.qty--
            toast.success("Quantity decreased")
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

    }
  })