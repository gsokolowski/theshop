<!-- Login component -->
<template>
  <div class="row justify-content-center">
      <div class="col-md-4 p-4">
          <div class="text-center mb-4">
              <h2>Login</h2>
          </div>
          <div class="row">
              <p>Login to your account to continue shopping</p>
          </div>
          <div class="row">
              <form @submit.prevent="handleSubmit" novalidate>
                  <!-- Email field -->
                  <div class="form-group mb-3">
                      <label for="email">Email</label>
                      <input 
                          type="email" 
                          class="form-control" 
                          id="email" 
                          v-model="formData.email"
                          placeholder="Enter your email"
                          required
                          autocomplete="email">
                  </div>
                  
                  <!-- Password field -->
                  <div class="form-group mb-3">
                      <label for="password">Password</label>
                      <input 
                          type="password" 
                          class="form-control" 
                          id="password" 
                          v-model="formData.password"
                          placeholder="Enter your password"
                          required
                          autocomplete="current-password">
                  </div>

                  <!-- Validation message -->
                  <div v-if="authStore.getValidationMessage" class="alert alert-danger" role="alert">
                        {{ authStore.getValidationMessage }}
                  </div>

                  <!-- Validation errors -->
                  <ValidationErrors 
                     :errors="authStore.getValidationErrors" 
                     :visible="false" 
                  />

                  <!-- Submit button -->
                  <div class="form-group d-grid mb-3">
                      <button 
                          type="submit" 
                          class="btn btn-primary"
                          :disabled="authStore.isLoading">
                          <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
                          {{ authStore.isLoading ? 'Logging in...' : 'Login' }}
                      </button>
                  </div>
                  
                  <!-- Divider -->
                  <div class="text-center mb-3">
                      <hr>
                      <span class="text-muted">OR</span>
                      <hr>
                  </div>
                  
                  <!-- Google Sign-In button -->
                  <div class="form-group d-grid mb-3">
                      <a 
                          :href="googleAuthUrl" 
                          class="btn btn-outline-danger"
                          style="display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;"
                      >
                          <svg width="18" height="18" viewBox="0 0 18 18">
                              <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/>
                              <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.965-2.184l-2.908-2.258c-.806.54-1.837.86-3.057.86-2.35 0-4.34-1.587-5.053-3.716H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                              <path fill="#FBBC05" d="M3.954 10.712c-.18-.54-.282-1.117-.282-1.712s.102-1.172.282-1.712V4.956H.957C.347 6.175 0 7.55 0 9s.348 2.825.957 4.044l2.997-2.332z"/>
                              <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.956L3.954 7.288C4.667 5.163 6.657 3.58 9 3.58z"/>
                          </svg>
                          Sign in with Google
                      </a>
                  </div>
                  
                  <!-- Link to register -->
                  <div class="text-center">
                      <p class="mb-0">
                          Don't have an account? 
                          <router-link to="/register">Register here</router-link>
                      </p>
                  </div>
              </form>
          </div>
      </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, computed } from 'vue'
import { useAuthStore } from '../../stores/useAuthStore'
import { useCartStore } from '../../stores/useCartStore'
import { useRouter } from 'vue-router'
import ValidationErrors from '../common/ValidationErrors.vue' 

const authStore = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()

const formData = reactive({
    email: '',
    password: ''
})

// Google OAuth URL
const googleAuthUrl = computed(() => {
    return 'http://127.0.0.1:8000/api/v1/auth/google'
})

const handleSubmit = async () => {
    // Clear any previous errors
    authStore.setValidationErrors({})
    
    try {
        await authStore.login({
            email: formData.email,
            password: formData.password
        })
        
        // Clear any previous cart data from localStorage to ensure user sees their own cart
        // This prevents User 2 from seeing User 1's cart items when logging in on the same computer
        cartStore.clearCart(false) // Don't show toast on login
        
        // Load user's cart from backend immediately after login
        await cartStore.fetchCart()

        // Redirect to home page after successful login
        router.push('/')
    } catch (error) {
        // Backend validation errors are handled in the store authStore.validationErrors and shown in the component Login.vue above
        // So you don't need to show a toast here
        console.error('Login error:', error)
    }
}

onMounted(() => {
    // Clear any previous errors
    authStore.setValidationErrors({})
    // Clear any previous validation message
    authStore.setValidationMessage('')
})
</script>

<style scoped>
</style>