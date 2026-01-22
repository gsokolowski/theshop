<template>
    <div class="row my-5">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-body p-5">
                    <h4 class="text-center">
                        Payment is done successfully
                    </h4>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { onMounted, computed } from "vue"
    import axios from "axios"
    import { useAuthStore } from "../../stores/useAuthStore"
    import { useCartStore } from "../../stores/useCartStore"
    import { useToast } from "vue-toastification"
    import { useRoute, useRouter } from "vue-router"

    //define the stores
    const authStore = useAuthStore()
    const cartStore = useCartStore()


    // define the computed properties
    const cartItems = computed(() => cartStore.cartItems)
    const user = computed(() => authStore.getUser)

    //define the toast
    const toast = useToast()

    //define the route and router
    const route = useRoute()
    const router = useRouter()

    //store user orders in DB
    const storeUserOrders = async () => {
        try {
            //Transform cartItems to match backend expectations
            const cartItemsData = cartItems.value.map(item => ({
                product_id: item.product.id,  // Extract product ID
                qty: item.qty,
                price: item.product.price,  // Backend needs price for calculation
                coupon_id: item.coupon_id || null
            }))
            console.log('Cart Items Data', cartItemsData)

            // call the api/orders endpoint to store user orders
            const response = await axios.post(`/api/orders`,
                {
                    cartItems : cartItemsData,
                },
                {
                    headers: {
                        'Authorization': `Bearer ${authStore.getAccessToken}`
                    }
                }
            )
            cartStore.clearCart()
            cartStore.removeCouponFromAllItems()

            if (response.data.data?.user) {
                authStore.setUser(response.data.data.user)
            }
            toast.success("Check your orders status in your profile tab",{
                timeout: 2000
            })
        } catch (error) {
            const errorMessage = error.response?.data?.message || 
                           error.response?.data?.error || 
                           'Error storing orders'
            toast.error(errorMessage)
            console.error('Error storing orders:', error)
        }
    }

    //once the component is mounted we store user orders
    onMounted(() => {
        // check if stored unique hash in the useCartStore is the same as the unique hash in the route
        if(cartStore.uniqueHash === route.params.hash) {
            // store user orders in DB
            storeUserOrders()
            // clear the unique hash in the useCartStore
            cartStore.setUniqueHash('')
        }else {
            router.push('/')
        }
    })

</script>

<style scoped>
</style>