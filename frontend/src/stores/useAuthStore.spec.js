import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useAuthStore } from './useAuthStore'

vi.mock('vue-toastification', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  }),
}))

// Axios mock with defaults for auth store (setToken, initializeAxiosHeaders, logout)
vi.mock('axios', () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
    defaults: {
      headers: { common: {} },
    },
  },
}))

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  describe('initial state', () => {
    it('has correct initial state', () => {
      const store = useAuthStore()
      expect(store.isUserLoggedIn).toBe(false)
      expect(store.user).toBe(null)
      expect(store.accessToken).toBe(null)
      expect(store.validationErrors).toEqual({})
      expect(store.validationMessage).toBe('')
      expect(store.isLoading).toBe(false)
    })
  })

  describe('getters', () => {
    it('getIsUserLoggedIn returns isUserLoggedIn state', () => {
      const store = useAuthStore()
      expect(store.getIsUserLoggedIn).toBe(false)
      store.isUserLoggedIn = true
      expect(store.getIsUserLoggedIn).toBe(true)
    })

    it('getUser returns user object', () => {
      const store = useAuthStore()
      const user = { id: 1, name: 'Test User' }
      store.user = user
      expect(store.getUser).toEqual(user)
    })

    it('getAccessToken returns access token', () => {
      const store = useAuthStore()
      store.accessToken = 'token123'
      expect(store.getAccessToken).toBe('token123')
    })

    it('getValidationErrors returns validation errors', () => {
      const store = useAuthStore()
      const errors = { email: ['Invalid email'] }
      store.validationErrors = errors
      expect(store.getValidationErrors).toEqual(errors)
    })

    it('getValidationMessage returns validation message', () => {
      const store = useAuthStore()
      store.validationMessage = 'Invalid credentials'
      expect(store.getValidationMessage).toBe('Invalid credentials')
    })

    it('getIsLoading returns isLoading state', () => {
      const store = useAuthStore()
      expect(store.getIsLoading).toBe(false)
      store.isLoading = true
      expect(store.getIsLoading).toBe(true)
    })
  })

  describe('actions - setters', () => {
    it('setUserLoggedIn updates isUserLoggedIn', () => {
      const store = useAuthStore()
      store.setUserLoggedIn(true)
      expect(store.isUserLoggedIn).toBe(true)
      store.setUserLoggedIn(false)
      expect(store.isUserLoggedIn).toBe(false)
    })

    it('setUser updates user', () => {
      const store = useAuthStore()
      const user = { id: 1, name: 'Test' }
      store.setUser(user)
      expect(store.user).toEqual(user)
    })

    it('setAccessToken updates accessToken', () => {
      const store = useAuthStore()
      store.setAccessToken('abc123')
      expect(store.accessToken).toBe('abc123')
    })

    it('setToken sets token and axios header', () => {
      const store = useAuthStore()
      store.setToken('bearer-token')
      expect(store.accessToken).toBe('bearer-token')
      expect(axios.defaults.headers.common['Authorization']).toBe('Bearer bearer-token')
    })

    it('setValidationErrors updates validationErrors', () => {
      const store = useAuthStore()
      const errors = { password: ['Too short'] }
      store.setValidationErrors(errors)
      expect(store.validationErrors).toEqual(errors)
    })

    it('setValidationMessage updates validationMessage', () => {
      const store = useAuthStore()
      store.setValidationMessage('Error message')
      expect(store.validationMessage).toBe('Error message')
    })

    it('setIsLoading updates isLoading', () => {
      const store = useAuthStore()
      store.setIsLoading(true)
      expect(store.isLoading).toBe(true)
    })

    it('initializeAxiosHeaders sets Authorization when token exists', () => {
      const store = useAuthStore()
      store.accessToken = 'my-token'
      store.initializeAxiosHeaders()
      expect(axios.defaults.headers.common['Authorization']).toBe('Bearer my-token')
    })

    it('initializeAxiosHeaders does nothing when no token', () => {
      const store = useAuthStore()
      store.accessToken = null
      if (axios.defaults?.headers?.common) {
        delete axios.defaults.headers.common['Authorization']
      }
      store.initializeAxiosHeaders()
      expect(axios.defaults?.headers?.common?.['Authorization']).toBeUndefined()
    })
  })

  describe('login', () => {
    it('calls API and sets user state on success', async () => {
      const store = useAuthStore()
      const mockUser = { id: 1, name: 'Test User', email: 'test@example.com' }
      vi.mocked(axios.post).mockResolvedValue({
        data: {
          user: mockUser,
          access_token: 'token123',
          message: 'Login successful',
        },
      })

      const result = await store.login({ email: 'test@example.com', password: 'password' })

      expect(axios.post).toHaveBeenCalledWith('/user/login', {
        email: 'test@example.com',
        password: 'password',
      })
      expect(store.user).toEqual(mockUser)
      expect(store.isUserLoggedIn).toBe(true)
      expect(store.accessToken).toBe('token123')
      expect(store.isLoading).toBe(false)
      expect(result.data.user).toEqual(mockUser)
    })

    it('sets validation errors on 422 failure', async () => {
      const store = useAuthStore()
      const errors = { email: ['The email field is required.'] }
      vi.mocked(axios.post).mockRejectedValue({
        response: { data: { errors: errors, message: 'Validation failed' }, status: 422 },
      })

      await expect(store.login({ email: '', password: '' })).rejects.toBeDefined()

      expect(store.validationErrors).toEqual(errors)
      expect(store.validationMessage).toBe('Validation failed')
      expect(store.isLoading).toBe(false)
    })
  })

  describe('register', () => {
    it('calls API and sets user on success', async () => {
      const store = useAuthStore()
      const mockUser = { id: 1, name: 'Test', email: 'test@example.com' }
      vi.mocked(axios.post).mockResolvedValue({
        data: {
          data: mockUser,
          message: 'Registration successful',
        },
      })

      const result = await store.register({
        name: 'Test',
        email: 'test@example.com',
        password: 'password',
        confirm_password: 'password',
      })

      expect(axios.post).toHaveBeenCalledWith('/user/register', {
        name: 'Test',
        email: 'test@example.com',
        password: 'password',
        confirm_password: 'password',
      })
      expect(store.user).toEqual(mockUser)
      expect(store.isLoading).toBe(false)
      expect(result.data.message).toBe('Registration successful')
    })

    it('sets validation errors on failure', async () => {
      const store = useAuthStore()
      const errors = { email: ['Email already taken'] }
      vi.mocked(axios.post).mockRejectedValue({
        response: { data: { errors } },
      })

      await expect(
        store.register({
          name: 'Test',
          email: 'used@example.com',
          password: 'password',
          confirm_password: 'password',
        })
      ).rejects.toBeDefined()

      expect(store.validationErrors).toEqual(errors)
      expect(store.isLoading).toBe(false)
    })
  })

  describe('logout', () => {
    it('clears state and calls logout API when token exists', async () => {
      const store = useAuthStore()
      store.user = { id: 1 }
      store.isUserLoggedIn = true
      store.accessToken = 'token123'
      axios.defaults.headers.common['Authorization'] = 'Bearer token123'

      vi.mocked(axios.post).mockResolvedValue({})

      await store.logout()

      expect(axios.post).toHaveBeenCalledWith(
        '/user/logout',
        {},
        { headers: { Authorization: 'Bearer token123' } }
      )
      expect(store.user).toBe(null)
      expect(store.isUserLoggedIn).toBe(false)
      expect(store.accessToken).toBe(null)
      expect(axios.defaults.headers.common['Authorization']).toBeUndefined()
    })

    it('clears state without API call when no token', async () => {
      const store = useAuthStore()
      store.user = { id: 1 }
      store.isUserLoggedIn = true
      store.accessToken = null

      await store.logout()

      expect(axios.post).not.toHaveBeenCalled()
      expect(store.user).toBe(null)
      expect(store.isUserLoggedIn).toBe(false)
    })

    it('clears state even when API fails', async () => {
      const store = useAuthStore()
      store.accessToken = 'token123'
      vi.mocked(axios.post).mockRejectedValue(new Error('Network error'))

      await store.logout()

      expect(store.user).toBe(null)
      expect(store.isUserLoggedIn).toBe(false)
      expect(store.accessToken).toBe(null)
    })
  })

  describe('getLoggedInUser', () => {
    it('fetches and sets user on success', async () => {
      const store = useAuthStore()
      const mockUser = { id: 1, name: 'Test' }
      vi.mocked(axios.get).mockResolvedValue({
        data: { user: mockUser, access_token: 'token123' },
      })

      const result = await store.getLoggedInUser()

      expect(axios.get).toHaveBeenCalledWith('/user')
      expect(store.user).toEqual(mockUser)
      expect(store.accessToken).toBe('token123')
      expect(store.isUserLoggedIn).toBe(true)
      expect(result).toEqual(mockUser)
    })

    it('clears state on 401', async () => {
      const store = useAuthStore()
      store.user = { id: 1 }
      store.accessToken = 'old-token'
      store.isUserLoggedIn = true
      axios.defaults.headers.common['Authorization'] = 'Bearer old-token'

      vi.mocked(axios.get).mockRejectedValue({ response: { status: 401, data: {} } })

      await expect(store.getLoggedInUser()).rejects.toBeDefined()

      expect(store.user).toBe(null)
      expect(store.isUserLoggedIn).toBe(false)
      expect(store.accessToken).toBe(null)
      expect(axios.defaults.headers.common['Authorization']).toBeUndefined()
    })
  })
})
