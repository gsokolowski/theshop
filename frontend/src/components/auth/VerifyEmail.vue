<template>
  <div class="row justify-content-center">
    <div class="col-md-6 p-4">
      <div class="text-center mb-4">
        <h2>Email Verification</h2>
      </div>
      
      <div v-if="isLoading" class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Verifying your email...</p>
      </div>
      
      <div v-else-if="verificationStatus === 'success'" class="alert alert-success" role="alert">
        <h4 class="alert-heading">
          <i class="bi bi-check-circle-fill me-2"></i>Email Verified Successfully!
        </h4>
        <p>Your email has been verified. You can now log in to your account.</p>
        <hr>
        <div class="d-grid">
          <router-link to="/login" class="btn btn-primary">Go to Login</router-link>
        </div>
      </div>
      
      <div v-else-if="verificationStatus === 'error'" class="alert alert-danger" role="alert">
        <h4 class="alert-heading">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>Verification Failed
        </h4>
        <p>{{ errorMessage }}</p>
        <hr>
        <div class="d-grid">
          <router-link to="/login" class="btn btn-primary">Go to Login</router-link>
        </div>
      </div>
      
      <div v-else-if="verificationStatus === 'already-verified'" class="alert alert-info" role="alert">
        <h4 class="alert-heading">
          <i class="bi bi-info-circle-fill me-2"></i>Already Verified
        </h4>
        <p>Your email is already verified. You can log in to your account.</p>
        <hr>
        <div class="d-grid">
          <router-link to="/login" class="btn btn-primary">Go to Login</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { apiBaseUrl } from '../../config/api.js'

const isLoading = ref(true)
const verificationStatus = ref(null)
const errorMessage = ref('')

onMounted(async () => {
  try {
    // Extract query parameters from URL - preserve the exact query string
    // Don't rebuild it, use the original query string from the URL
    const originalQuery = window.location.search // Gets "?id=1&expires=xxx&signature=xxx"
    
    // Build the verification URL using the exact query string from the email link
    const verificationUrl = `${apiBaseUrl}/email/verify${originalQuery}`
    
    // Call the verification API
    const response = await axios.get(verificationUrl)
    
    if (response.data.status === 200) {
      if (response.data.message === 'Email is already verified') {
        verificationStatus.value = 'already-verified'
      } else {
        verificationStatus.value = 'success'
      }
    } else {
      verificationStatus.value = 'error'
      errorMessage.value = response.data.error || 'Verification failed'
    }
  } catch (error) {
    verificationStatus.value = 'error'
    if (error.response?.data?.error) {
      errorMessage.value = error.response.data.error
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'An error occurred while verifying your email. Please try again.'
    }
  } finally {
    isLoading.value = false
  }
})
</script>

<style scoped>
.alert {
  margin-top: 20px;
}
</style>
