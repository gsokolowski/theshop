<!-- Register component -->
<template>
  <div class="row justify-content-center">
      <div class="col-md-4 p-4">
          <div class="text-center mb-4">
              <h2>Register</h2>
          </div>
          <div class="row">
              <p>Register your account to start shopping</p>
          </div>
          <div class="row">
              <form @submit.prevent="handleSubmit" novalidate>
                  <!-- Name field -->
                  <div class="form-group mb-3">
                      <label for="name">Name</label>
                      <input 
                          type="text" 
                          class="form-control" 
                          id="name" 
                          v-model="formData.name"
                          placeholder="Name*"
                          required>
                  </div>
                  
                  <!-- Email field -->
                  <div class="form-group mb-3">
                      <label for="email">Email</label>
                      <input 
                          type="email" 
                          class="form-control" 
                          id="email" 
                          v-model="formData.email"
                          placeholder="Email*"
                          required>
                  </div>
                  
                  <!-- Password field -->
                  <div class="form-group mb-3">
                      <label for="password">Password</label>
                      <input 
                          type="password" 
                          class="form-control" 
                          id="password" 
                          v-model="formData.password"
                          placeholder="Password*"
                          required>
                  </div>
                  
                  <!-- Confirm Password field -->
                  <div class="form-group mb-3">
                      <label for="confirm_password">Confirm Password</label>
                      <input 
                          type="password" 
                          class="form-control" 
                          id="confirm_password" 
                          v-model="formData.confirm_password"
                          placeholder="Confirm Password*"
                          required>
                  </div>
                  
                  <!-- Validation message invisible by default -->
                  <div v-if="authStore.getValidationMessage" class="alert alert-danger" role="alert" style="display: none;">
                        {{ authStore.getValidationMessage }}
                  </div>

                  <!-- Validation errors -->
                  <ValidationErrors 
                     :errors="authStore.getValidationErrors" 
                     :visible="true" 
                  />
                  
                  <!-- Submit button -->
                  <div class="form-group d-grid mb-3">
                    <button 
                        type="submit" 
                        class="btn btn-primary"
                        :disabled="authStore.isLoading">
                        <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
                        {{ authStore.isLoading ? 'Registering...' : 'Register' }}
                    </button>
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
import ValidationErrors from '../common/ValidationErrors.vue' // ✅ Import the component

const authStore = useAuthStore()
const router = useRouter()

const formData = reactive({
  name: '',
  email: '',
  password: '',
  confirm_password: ''
})

const handleSubmit = async () => {
  // Clear any previous errors
  authStore.setValidationErrors({})
  authStore.setValidationMessage('')
  
  console.log('=== FORM COMPONENT DEBUG ===')
  console.log('All form data:', formData.value)
  
  try {
      // make actual API call to register the user
      await authStore.register({
          name: formData.name.trim(),
          email: formData.email.trim(),
          password: formData.password,
          confirm_password: formData.confirm_password
      })
      
      // Redirect to login page after successful registration
      router.push('/login')

  } catch (error) {
      // Backend validation errors are handled in the store authStore.errorMessage and shown in the component Registration.vue above
      // So you don't need to show a toast here
      console.error('Registration error:', error)
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