<template>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div v-if="cartStore.validCoupon.coupon_id" class="d-flex align-items-center gap-2 w-100">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold me-3">Coupon</span>
                        <span class="fw-bold me-3">{{ cartStore.validCoupon.name }}</span>
                    </div>
                    <button 
                        class="btn btn-sm btn-outline-danger ms-auto"
                        @click="removeCoupon"
                        :disabled="cartStore.isLoading"
                        title="Remove coupon"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <template v-else>
                    <span class="fw-bold me-5">Coupon</span>
                    <!-- ✅ CHANGED: Applied same styling as profileImageForm -->
                    <div class="d-flex gap-2">
                        <input 
                            type="text" 
                            class="form-control form-control-sm" 
                            v-model="data.coupon.name" 
                            placeholder="Coupon Name"
                            @keyup.enter="applyCoupon"
                            :disabled="cartStore.isLoading"
                        >
                        <button 
                            class="btn btn-primary btn-sm flex-fill"
                            @click="applyCoupon"
                            :disabled="!data.coupon.name || data.coupon.name.trim() === '' || cartStore.isLoading"
                        >
                            <span v-if="cartStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
                            {{ cartStore.isLoading ? 'Applying...' : 'Apply' }}
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useCartStore } from '../../stores/useCartStore'
import { useAuthStore } from '../../stores/useAuthStore'
import axios from 'axios'


const cartStore = useCartStore()
const authStore = useAuthStore()

// define the data object
const data = reactive({
    coupon: {
        name: null,
    }
})

// define the toast
const toast = useToast()


// define the method to apply the coupon
const applyCoupon = async () => {
    console.log('applyCoupon called')
    // check if the coupon name is empty
    if (!data.coupon.name || data.coupon.name.trim() === '') {
        toast.error('Please enter a coupon name')
        return
    }
    try {
        cartStore.isLoading = true
        console.log('Coupon Name', data.coupon.name)
        const response = await axios.get(`/api/coupon/${data.coupon.name}`,
            {
                // you don't need to set the headers here because it is already set in the main.js file
                // authStore.initializeAxiosHeaders()
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Authorization': `Bearer ${authStore.getAccessToken}`,
                }
            }
        )

        console.log('coupon response', response)
        if (response.status == 404) {
            toast.error(response.data.error)
            data.coupon.name = null
        }
        else {
            toast.success(response.data.message)
            // set the valid coupon in the cart store
            cartStore.setValidCoupon({
                coupon_id: response.data.data.id,
                name: response.data.data.name,
                discount: response.data.data.discount,
                valid_until: response.data.data.valid_until,
            })
            // add the coupon to the cart items in the cart store
            cartStore.addCouponToCartItem(response.data.data.id)
            // ✅ ADDED: Clear input field after successful application
            data.coupon.name = null
        }

    } catch (error) {
        if (error.response?.status === 404) {
            toast.error(error.response?.data?.error || 'Coupon not found or expired')
        } else {
            toast.error('Failed to apply coupon. Please try again.')
        }
        console.error('Error applying coupon:', error)
    } finally {
        cartStore.isLoading = false
    }
}

// ✅ ADDED: Method to remove coupon from all cart items
const removeCoupon = () => {
    cartStore.removeCouponFromAllItems()
    data.coupon.name = null
    toast.info('Coupon removed')
}
// on mount, set the coupon name to the coupon name in the data object
onMounted(() => {
    // remove the coupon from all cart items on mount
    cartStore.removeCouponFromAllItems()
    // set the coupon name to the coupon name in the data object
    if (cartStore.validCoupon?.name) {
        data.coupon.name = cartStore.validCoupon.name
    }
})

</script>

<style scoped>
    .gap-2 {
        gap: 2rem;
    }
</style>