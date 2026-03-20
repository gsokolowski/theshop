import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useAuthStore } from '../../stores/useAuthStore'
import { useCartStore } from '../../stores/useCartStore'
import Login from './Login.vue'

const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/login', component: { template: '<div>Login</div>' } },
  { path: '/register', component: { template: '<div>Register</div>' } },
]

function mountLogin() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(Login, {
    global: {
      plugins: [pinia, router],
      stubs: { ValidationErrors: true },
    },
  })
}

describe('Login', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders the Login form', () => {
    const wrapper = mountLogin()
    expect(wrapper.find('h2').text()).toBe('Login')
    expect(wrapper.text()).toContain('Login to your account to continue shopping')
  })

  it('renders email and password inputs', () => {
    const wrapper = mountLogin()
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#password').exists()).toBe(true)
    expect(wrapper.find('#email').attributes('type')).toBe('email')
    expect(wrapper.find('#password').attributes('type')).toBe('password')
  })

  it('renders Register link', () => {
    const wrapper = mountLogin()
    const registerLink = wrapper.findComponent({ name: 'RouterLink' })
    expect(registerLink.exists()).toBe(true)
    expect(registerLink.props('to')).toBe('/register')
    expect(wrapper.text()).toContain('Register here')
  })

  it('renders Sign in with Google button', () => {
    const wrapper = mountLogin()
    expect(wrapper.text()).toContain('Sign in with Google')
    expect(wrapper.find('a[href*="google"]').exists()).toBe(true)
  })

  it('calls authStore.login on form submit', async () => {
    const wrapper = mountLogin()
    const authStore = useAuthStore()
    const cartStore = useCartStore()
    vi.spyOn(authStore, 'login').mockResolvedValue({})
    vi.spyOn(cartStore, 'clearCart')
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})

    await wrapper.find('#email').setValue('test@example.com')
    await wrapper.find('#password').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')

    expect(authStore.login).toHaveBeenCalledWith({
      email: 'test@example.com',
      password: 'password123',
    })
  })
})
