<template>
    <div class="row">
      <div class="col-md-12">
          <button class="btn btn-primary btn-block"
              @click="fetchStripePaymentLink"
              >
              Proceed to payment
          </button>
      </div>
    </div>
  </template>

<script setup>
    import { useAuthStore } from '../../stores/useAuthStore'
    import { useCartStore } from '../../stores/useCartStore'
    import axios from 'axios' // for api calls  
    import { makeUniqueId } from '../helpers/config'
    import { computed } from 'vue'
    import { useToast } from 'vue-toastification' 

    // define stores
    const authStore = useAuthStore()
    const cartStore = useCartStore()
    
    // define toast
    const toast = useToast() 

    // define the computed properties
    const cartItems = computed(() => cartStore.cartItems)
    const user = computed(() => authStore.getUser)

    // once user paid for his orders we will redirect him to the success page and store orders in db
    // we will use the hash to identify the order and redirect him to the success page
    // or if the hash is not found or not the same as the one in the url we will redirect him to the cancel page
    
    // define hash
    const hash = makeUniqueId(24)
    console.log('Hash', hash)
    
    // define the success and cancel urls hash as a route parameter
    const successUrl = computed(() => `${window.location.origin}/success/payment/${hash}`)
    // define the cancel url
    const cancelUrl = computed(() => `${window.location.origin}/cancel/payment`)

    // define the methods
    const fetchStripePaymentLink = async () => {
        try {
            //Transform cartItems to match backend expectations
            const cartItemsData = cartItems.value.map(item => ({
                product_id: item.product.id,  // Extract product ID
                qty: item.qty,
                price: item.product.price,  // Backend needs price for calculation
                coupon_id: item.coupon_id || null
            }))
            console.log('Cart Items Data', cartItemsData)
            // call the api/orders/pay endpoint to get the payment link payOrdersByStripe()
            const response = await axios.post('/api/orders/pay', {
                cartItems: cartItemsData,
                success_url: successUrl.value,
                cancel_url: cancelUrl.value
            }, {
                headers: {
                    'Authorization': `Bearer ${authStore.getAccessToken}`
                }
            })
            cartStore.setUniqueHash(hash)
            // redirect to the payment link from the response defined in the backend
            window.location.href = response.data.data.url
        } catch (error) {
            console.error('Error fetching payment link:', error)
            toast.error('Error fetching payment link')
        }
    }
</script>

<style scoped>

</style>