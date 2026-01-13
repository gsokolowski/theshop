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
import { onMounted, reactive } from 'vue'
import { useAuthStore } from '../../stores/useAuthStore'
import { useRouter } from 'vue-router'
import ValidationErrors from '../common/ValidationErrors.vue' 

const authStore = useAuthStore()
const router = useRouter()

const formData = reactive({
    email: '',
    password: ''
})

const handleSubmit = async () => {
    // Clear any previous errors
    authStore.setValidationErrors({})
    
    try {
        await authStore.login({
            email: formData.email,
            password: formData.password
        })
        
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