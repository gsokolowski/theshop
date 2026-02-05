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
                      <div class="position-relative">
                          <input 
                              :type="showPassword ? 'text' : 'password'"
                              class="form-control" 
                              id="password" 
                              v-model="formData.password"
                              placeholder="Password*"
                              required>
                          <button 
                              type="button"
                              class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3"
                              style="border: none; background: none; z-index: 10;"
                              @click="showPassword = !showPassword">
                              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                          </button>
                      </div>
                  </div>
                  
                  <!-- Confirm Password field -->
                  <div class="form-group mb-3">
                      <label for="confirm_password">Confirm Password</label>
                      <div class="position-relative">
                          <input 
                              :type="showConfirmPassword ? 'text' : 'password'"
                              class="form-control" 
                              id="confirm_password" 
                              v-model="formData.confirm_password"
                              placeholder="Confirm Password*"
                              required>
                          <button 
                              type="button"
                              class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3"
                              style="border: none; background: none; z-index: 10;"
                              @click="showConfirmPassword = !showConfirmPassword">
                              <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                          </button>
                      </div>
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
                  
                  <!-- Success message after registration -->
                  <div v-if="showSuccessMessage" class="alert alert-success mt-3" role="alert">
                    <h5 class="alert-heading">
                      <i class="bi bi-envelope-check me-2"></i>Registration Successful!
                    </h5>
                    <p class="mb-2">We've sent a verification email to <strong>{{ formData.email }}</strong></p>
                    <p class="mb-0">Please check your inbox and click the verification link to activate your account.</p>
                    <hr>
                    <p class="mb-0">
                      <small>Didn't receive the email? 
                        <a href="#" @click.prevent="handleResendVerification" class="alert-link">Resend verification email</a>
                      </small>
                    </p>
                  </div>
                  
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
import { onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '../../stores/useAuthStore'
import { useRouter } from 'vue-router'
import ValidationErrors from '../common/ValidationErrors.vue' // ✅ Import the component

const authStore = useAuthStore()
const router = useRouter()

// ✅ ADDED: Password visibility state
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const showSuccessMessage = ref(false)

const formData = reactive({
  name: '',
  email: '',
  password: '',
  confirm_password: ''
})

const handleSubmit = async () => {
  // Clear any previous errors and success message
  authStore.setValidationErrors({})
  authStore.setValidationMessage('')
  showSuccessMessage.value = false
  
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
      
      // ✅ ADDED: Show success message instead of redirecting immediately
      showSuccessMessage.value = true
      
      // Clear form after successful registration
      formData.name = ''
      formData.email = ''
      formData.password = ''
      formData.confirm_password = ''

  } catch (error) {
      // Backend validation errors are handled in the store authStore.errorMessage and shown in the component Registration.vue above
      // So you don't need to show a toast here
      console.error('Registration error:', error)
  }
}

// ✅ ADDED: Handle resend verification email
const handleResendVerification = async () => {
  // This would require the user to be logged in, so we'll redirect to login
  // In a real scenario, you might want to store the email temporarily or use a different approach
  router.push('/login')
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