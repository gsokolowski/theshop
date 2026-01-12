<template>
  <div>
    <h1>Profile</h1>
    <Spinner :store="authStore" />
  </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/useAuthStore'    
import { onMounted } from 'vue'
import Spinner from '../layouts/Spinner.vue'

const authStore = useAuthStore()




onMounted( async () => {
    // clear validation error messages in the authStore
    authStore.setValidationErrors({})
    authStore.setValidationMessage('')
    try {
        const user = await authStore.getLoggedInUser();               
        console.log(user)
    } catch (error) {
        console.error('Profile error:', error)
    }
})

</script>

<style scoped>
</style>