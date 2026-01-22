<template>
    <div>
        <div class="row">
            <ProfileUpdate :updateProfile="false" />
            <div class="col-md-4 bg-light p-4">
            <ul class="list-group">
                <li class="list-group-item d-flex"
                    v-for="item in cartStore.getCartItems"
                    :key="item.reference"
                >
                    <img :src="item.product.thumbnail" 
                        width="100" 
                        class="img-fluid rounded me-2"
                        :alt="item.product.name"    
                    >
                    <div class="d-flex flex-column">
                        <h6 class="my-1">
                            <strong>{{ item.product.name }}</strong>
                        </h6>
                        <span class="text-normal">
                            <span class="text-normal">Color: {{ item.color.name }}</span>
                        </span>
                        <span class="text-normal">
                            <span>Size: {{ item.size.name }}</span>
                        </span>
                    </div>
                    <div class="d-flex flex-column ms-auto">
                        <span class="text-normal">
                            ${{ item.product.price }} <i>x</i> {{ item.qty }}
                        </span>
                        <span class="text-normal fw-bold">
                            ${{ item.product.price * item.qty }}
                        </span>
                    </div>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <!-- Coupon component -->
                    <Coupon />
                </li>
                <li v-if="cartTotalDiscount > 0" class="list-group-item d-flex justify-content-between">
                    <span class="fw-bold">
                        Discount {{ cartStore.validCoupon.discount }} %
                    </span>
                    <span class="fw-bold text-danger">
                        -${{ cartTotalDiscount.toFixed(2) }}
                    </span>
                </li>                    
                <li class="list-group-item d-flex justify-content-between">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold text-normal">${{ cartTotalPrice.toFixed(2) }}</span>
                </li>
            </ul>
            <!-- Stripe component button with link to stripe payment page -->
            <Stripe 
                v-if="user.profile_completed == true && cartTotalItems > 0"
                />
            </div>
        </div>        
    </div>
</template>

<script setup>
    import { computed, onMounted } from 'vue';
    import { useCartStore } from '../../stores/useCartStore'
    import { useAuthStore } from '../../stores/useAuthStore';
    import { useRouter } from 'vue-router';
    import { useToast } from 'vue-toastification';
    import ProfileUpdate from '../profile/ProfileUpdate.vue';
    import Coupon from '../coupons/Coupon.vue';
    import axios from 'axios'; // for API calls
    import Stripe from '../payment/Stripe.vue';

    // define the stores
    const cartStore = useCartStore()
    const authStore = useAuthStore()

    // define router
    const router = useRouter()

    // define the computed properties
    const cartItems = computed(() => cartStore.cartItems)
    const user = computed(() => authStore.getUser)
    const isUserLoggedIn = computed(() => authStore.isUserLoggedIn)

    // define toast
    const toast = useToast()

    // define the computed properties
    const cartTotalItems = computed(() => cartItems.value.length)
    const cartTotalDiscount = computed(() => cartItems.value.reduce((total, item) => total + (item.product.price * item.qty * cartStore.validCoupon.discount / 100), 0))
    const cartTotalPrice = computed(() => cartItems.value.reduce((total, item) => total + (item.product.price * item.qty), 0) - cartTotalDiscount.value)

    
    // define the mounted hook
    onMounted(() => {
        console.log('Cart Items', cartItems.value)
        console.log('User', user.value)
        console.log('Is User Logged In', isUserLoggedIn.value)
        
        // redirect user to home page if cartTotalItems is 0
        if (cartTotalItems.value <= 0) {
            toast.error('Your cart is empty')
            router.push('/')
            return
        }

    })
</script>

<style scoped>

</style>