import { defineStore } from 'pinia'
import axios from 'axios'
import { useToast } from 'vue-toastification'
// define toast
const toast = useToast()

export const useAuthStore = defineStore('auth', {
    state: () => ({
        isUserLoggedIn: false,
        user: null,
        accessToken: null,
        validationErrors: {}, // object to store validation errors from backend
        validationMessage: '', // string to store validation message from backend
        isLoading: false,
    }),
    persist: true,
    getters: {
        getIsUserLoggedIn: (state) => state.isUserLoggedIn,      // Returns isUserLoggedIn state - boolean
        getUser: (state) => state.user,      // Returns currently logged in user object
        getAccessToken: (state) => state.accessToken,      // Returns accessToken string
        getValidationErrors: (state) => state.validationErrors,      // Returns validationErrors object
        getValidationMessage: (state) => state.validationMessage,      // Returns validationMessage string
        getIsLoading: (state) => state.isLoading,      // Returns isLoading state - boolean
    },
    actions: {
        // set isUserLoggedIn state to true if user is logged in, false if not
        setUserLoggedIn(value) {
            this.isUserLoggedIn = value
        },
        // set currently logged in user object
        setUser(user) {
            this.user = user
        },
        // setAccessToken state to the access token string
        setAccessToken(accessToken) {
            this.accessToken = accessToken
        },
        // set validationErrors state to the validation errors object
        setValidationErrors(validationErrors) {
            this.validationErrors = validationErrors
        },
        // set validationMessage state to the validation message string
        setValidationMessage(validationMessage) {
            this.validationMessage = validationMessage
        },
        // set isLoading state to true or false
        setIsLoading(value) {
            this.isLoading = value
        },
        // Initialize axios headers from persisted state
        initializeAxiosHeaders() {
            if (this.accessToken) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.accessToken}`
            }
        },
        // Action to login the user and set the user object and isLoggedIn state to true
        async login(credentials) {
            this.setIsLoading(true)
            this.setValidationErrors({})
            this.setValidationMessage('')
            
            try {
                const response = await axios.post('/api/user/login', {
                    email: credentials.email,
                    password: credentials.password
                })
                // response.data returns data with access_token and user object
                console.log('Login successful response:', response.data)
                
                // set the user object and isLoggedIn state to true and the access token state to the access token string
                this.setUser(response.data.user)
                this.setUserLoggedIn(true)
                this.setAccessToken(response.data.access_token)
                this.setIsLoading(false)

                // Set axios default header for future requests
                this.initializeAxiosHeaders() // initialize axios headers from persisted state
                            
                // Show success toast
                toast.success(response.data.message + ' Welcome back!')

                return response

            } catch (error) {
                // Handle validation errors (422) or authentication errors (401)
                // Prioritize specific errors over generic message
                const validationErrors = error.response?.data?.errors ? error.response.data.errors : {}
                const validationMessage = error.response?.data?.message ? error.response.data.message : ''
                
                this.setValidationErrors(validationErrors)
                this.setValidationMessage(validationMessage)
                this.setIsLoading(false)
                console.log('Validation message:', validationMessage)
                throw error // Re-throw so component can handle it
            }
        },
        // Action to register the user and set the user object and isLoggedIn state to true
        async register(credentials) {
            this.setIsLoading(true)
            this.setValidationErrors({})
            
            console.log('=== STORE DEBUG ===')
            console.log('Credentials received:', credentials)

            try {
                const response = await axios.post('/api/user/register', {
                    name: credentials.name,
                    email: credentials.email,
                    password: credentials.password,
                    confirm_password: credentials.confirm_password,
                })
                
                // Registration successful - set user data (but don't log in yet, no token returned)
                this.setUser(response.data.user)
                this.setValidationErrors({})
                this.setIsLoading(false)
                
                // Show success toast
                toast.success(response.data.message + ' Please login to continue.')
                
                return response
                
            } catch (error) {
                // Handle validation errors (422) or other errors
                const validationErrors = error.response?.data?.errors ? error.response.data.errors : {}
                this.setValidationErrors(validationErrors)
                this.setIsLoading(false)
                
                // Backend validation errors are handled and shown in the component Registration.vue
                console.error('Registration error:', validationErrors)
                
                throw error // Re-throw so component can handle it
            }
        },
        // Action to logout the user and set the user object and isLoggedIn state to false
        async logout() {
            this.setIsLoading(true)
            this.setValidationErrors({})  

            try {
                // Call logout endpoint if token exists
                if (this.accessToken) {
                    await axios.post('/api/user/logout', {}, {
                        headers: {
                            'Authorization': `Bearer ${this.accessToken}`
                        }
                    })
                }
            } catch (error) {
                console.error('Logout error:', error)
                this.setValidationErrors({})
                this.setIsLoading(false)
                // Continue with logout even if API call fails
            } finally {
                // Clear state regardless of API response
                this.setUser(null)
                this.setUserLoggedIn(false)
                this.setAccessToken(null)
                this.setIsLoading(false)
                
                // Remove axios authorization header
                delete axios.defaults.headers.common['Authorization']
            }
        },
        // Action to get the currently logged in user object
        async getLoggedInUser() {
            this.setIsLoading(true)
            this.setValidationErrors({})
            
            try {
                const response = await axios.get('/api/user')
                
                // Update user data and access token
                this.setUser(response.data.user)
                this.setAccessToken(response.data.access_token)
                this.setUserLoggedIn(true)
                this.setValidationErrors({})
                this.setIsLoading(false)
                
                // Update axios default header with current token
                if (response.data.access_token) {
                    axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`
                }
                
                return response.data.user
                
            } catch (error) {
                // Handle authentication errors (401) or other errors
                const validationErrors = error.response?.data?.errors ? error.response.data.errors : {}
                
                // If 401 Unauthorized, user is not authenticated - clear state
                if (error.response?.status === 401) {
                    this.setUser(null)
                    this.setUserLoggedIn(false)
                    this.setAccessToken(null)
                    delete axios.defaults.headers.common['Authorization']
                    this.setValidationErrors({})
                } else {
                    this.setValidationErrors(validationErrors)
                }
                
                this.setIsLoading(false)
                throw error // Re-throw so component can handle it
            }
        },
    }
})