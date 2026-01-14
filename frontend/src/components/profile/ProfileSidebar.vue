<template>
    <div class="col-md-4 bg-light p-4">
        <div class="text-center">
            <Spinner :store="authStore" />
            <!-- Profile Image -->
            <div class="card p-2">                
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <img 
                        :src="user?.profile_image" 
                        :alt="user?.name" 
                        width="200"
                        height="200"
                        class="rounded-circle mb-3"
                        style="object-fit: cover; cursor: pointer;" 
                        @click="showForm = !showForm"
                        title="Click to change profile image"
                    >
                    <form 
                        name="profileImageForm"
                        v-if="showForm"
                        @submit.prevent="handleImageUpdate" 
                        enctype="multipart/form-data" 
                        class="w-100"
                    >
                        <div class="mb-3">
                            <input 
                                type="file" 
                                class="form-control form-control-sm" 
                                accept="image/*"
                                @change="handleFileSelect"
                                ref="fileInput"
                            >
                        </div>
                        <div class="d-flex gap-2">
                            <button 
                                type="submit" 
                                class="btn btn-primary btn-sm flex-fill"
                                :disabled=" ! formData.profile_image || authStore.isLoading"
                            >
                                <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
                                {{ authStore.isLoading ? 'Uploading...' : 'Update' }}
                            </button>

                        </div>
                    </form>
                    
                    <ul class="list-group w-100 text-center mt-2">
                        <li class="list-group-item">
                             {{ user?.name }}
                        </li>
                        <li class="list-group-item">
                             {{ user?.email }}
                        </li>
                        <li class="list-group-item">
                        <router-link to="/user/orders" class="text-decoration-none text-dark">
                            <i class="bi bi-bag-check-fill"></i> Orders
                        </router-link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed, reactive, ref } from 'vue'
import { useAuthStore } from '../../stores/useAuthStore'    
import Spinner from '../common/Spinner.vue'
import { useToast } from 'vue-toastification'
import ValidationErrors from '../../components/common/ValidationErrors.vue' 
import axios from 'axios'

const authStore = useAuthStore()
const toast = useToast()
const fileInput = ref(null)

// Get logged in user from store
const user = computed(() => authStore.getUser)

// Form visibility state
const showForm = ref(false)

// Form data for image upload
const formData = reactive({
    profile_image: null
})

// Handle file selection
const handleFileSelect = (event) => {
    formData.profile_image = event.target.files[0]
}

// Handle image update
const handleImageUpdate = async () => {
    if (!formData.profile_image) {
        toast.error('Please select an image')
        return
    }
    console.log('handleImageUpdate called')

    // Create FormData for file upload
    const uploadData = new FormData()
    uploadData.append('profile_image', formData.profile_image)

    try {
        authStore.setIsLoading(true)
        
        const response = await axios.put('/api/user/profile/update', uploadData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${authStore.getAccessToken}`
            }
        })

        // Update user in store
        authStore.setUser(response.data.user)
        
        // Hide form after successful upload
        // Clear file input
        formData.profile_image = null
        showForm.value = false
        if (fileInput.value) {
            fileInput.value.value = ''
        }
        
        toast.success(response.data.message || 'Profile image updated successfully')
    } catch (error) {
        console.error('Image update error:', error)
        const errorMessage = error.response?.data?.message || 'Failed to update profile image'
        toast.error(errorMessage)
    } finally {
        authStore.setIsLoading(false)
    }
}

onMounted(() => {
    // Clear any previous errors
    authStore.setValidationErrors({})
    authStore.setValidationMessage('')
})
</script>

<style scoped>
</style>