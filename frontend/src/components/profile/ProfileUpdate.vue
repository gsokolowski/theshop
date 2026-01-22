<template>
    <div class="col-md-8 bg-light p-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0 text-center">
                            {{ updateProfile ? 'Update Profile' : 'Billing Address' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- <Spinner :store="authStore" /> -->
                        <ValidationErrors :errors="authStore.validationErrors" />
                        <form name="profileUpdateForm" @submit.prevent="handleProfileUpdateSubmit" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="name" 
                                    v-model="formData.name" 
                                    required
                                    autocomplete="name"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="address" 
                                    v-model="formData.address" 
                                    required
                                    autocomplete="address"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="city" class="form-label">City</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="city" 
                                    v-model="formData.city" 
                                    required
                                    autocomplete="city"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="country" 
                                    v-model="formData.country" 
                                    required
                                    autocomplete="country"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="zip_code" class="form-label">Zip Code</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="zip_code" 
                                    v-model="formData.zip_code" 
                                    required
                                    autocomplete="zip_code"
                                />
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="phone_number" 
                                    v-model="formData.phone_number" 
                                    required
                                    autocomplete="phone_number"
                                />
                            </div>
                            <div class="d-flex justify-content-end">
                                <button
                                v-if="updateProfile"
                                type="submit" 
                                class="btn btn-primary"
                                :disabled="authStore.isLoading"
                                >
                                    <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
                                    {{ authStore.isLoading ? 'Updating...' : 'Update Profile' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed, onMounted, reactive } from 'vue'
    import { useAuthStore } from '../../stores/useAuthStore'
    import { useToast } from 'vue-toastification'
    import ValidationErrors from '../common/ValidationErrors.vue'
    import Spinner from '../../components/common/Spinner.vue'
    import axios from 'axios'
    
    const authStore = useAuthStore()
    const user = computed(() => authStore.getUser)

    // Define the form data
    const formData = reactive({
        name: '',
        address: '',
        city: '',
        country: '',
        zip_code: '',
        phone_number: '',
    })

    // define the props
    const props = defineProps({
        updateProfile: {
            type: Boolean,
            required: false,
            default: false
        }
    })

    // define toast
    const toast = useToast()

    
    // handle submit
    const handleProfileUpdateSubmit = async () => {
        console.log('handleProfileUpdateSubmit called')
        try {
            authStore.setIsLoading(true)

            console.log('Form Data 1:', formData)
            console.log('Access Token 1:', authStore.getAccessToken)
            const response = await axios.put('/api/user/profile/update', formData, {
                headers: {
                    'Authorization': `Bearer ${authStore.getAccessToken}`
                }
            })
            toast.success('Profile updated successfully')

            // Update user in store
            authStore.setUser(response.data.user)
            
            // Clear any previous errors
            authStore.setValidationErrors({})
            // Clear any previous validation message
            authStore.setValidationMessage('')
            authStore.setIsLoading(false)
        } catch (error) {
            console.error('Error updating profile:', error)
            authStore.setValidationErrors(error.response.data.errors)
            authStore.setValidationMessage(error.response.data.message)
            authStore.setIsLoading(false)
        }
    }
    
    console.log('Current User:', authStore.user)

    onMounted(() => {
        // Clear any previous errors
        authStore.setValidationErrors({})
        authStore.setValidationMessage('')
        
        // Populate form with user's current data
        if (user.value) {
            formData.name = user.value.name || ''
            formData.address = user.value.address || ''
            formData.city = user.value.city || ''
            formData.country = user.value.country || ''
            formData.zip_code = user.value.zip_code || ''
            formData.phone_number = user.value.phone_number || ''
        }
    })

</script>

<style scoped>
</style>