<template>
    <div class="row justify-content-center">
        <div class="col-md-4 p-4 text-center">
            <Spinner :store="authStore" />
            <div v-if="!authStore.isLoading">
                <h3>Authenticating...</h3>
                <p>Please wait while we log you in.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/useAuthStore'
import { useCartStore } from '../../stores/useCartStore'
import { useToast } from 'vue-toastification'
import Spinner from '../common/Spinner.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const toast = useToast()

onMounted(async () => {
    authStore.setIsLoading(true)
    
    const token = Array.isArray(route.query.token) ? route.query.token[0] : route.query.token
    const error = Array.isArray(route.query.error) ? route.query.error[0] : route.query.error
    
    if (error) {
        authStore.setIsLoading(false)
        toast.error(decodeURIComponent(error))
        router.push('/login')
        return
    }
    
    if (token) {
        try {
            authStore.setToken(token)
            await authStore.getLoggedInUser()

            cartStore.clearCart(false)
            await cartStore.fetchCart()

            authStore.setIsLoading(false)
            toast.success('Successfully signed in with Google!')
            router.push('/')
        } catch (err) {
            console.error('Error processing Google callback:', err)
            authStore.setIsLoading(false)
            toast.error('Failed to process authentication')
            router.push('/login')
        }
    } else {
        authStore.setIsLoading(false)
        toast.error('Authentication failed. Missing token.')
        router.push('/login')
    }
})
</script>

<style scoped>
</style>
