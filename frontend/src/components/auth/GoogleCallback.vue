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
    
    const token = route.query.token
    const userData = route.query.user
    const error = route.query.error
    
    if (error) {
        authStore.setIsLoading(false)
        toast.error(decodeURIComponent(error))
        router.push('/login')
        return
    }
    
    if (token && userData) {
        try {
            // Decode user data
            const user = JSON.parse(atob(userData))
            
            // Set token and user in store
            authStore.setToken(token)
            authStore.setUser(user)
            authStore.setUserLoggedIn(true)
            
            // Clear cart and fetch user's cart
            cartStore.clearCart(false) // Don't show toast
            await cartStore.fetchCart()
            
            authStore.setIsLoading(false)
            
            // Show success message
            toast.success('Successfully signed in with Google!')
            
            // Redirect to home
            router.push('/')
        } catch (error) {
            console.error('Error processing Google callback:', error)
            authStore.setIsLoading(false)
            toast.error('Failed to process authentication')
            router.push('/login')
        }
    } else {
        authStore.setIsLoading(false)
        toast.error('Authentication failed. Missing token or user data.')
        router.push('/login')
    }
})
</script>

<style scoped>
</style>
