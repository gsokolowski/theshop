import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useAuthStore } from '../../stores/useAuthStore'
import Register from './Register.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/login', component: { template: '<div>Login</div>' } },
  { path: '/register', component: { template: '<div>Register</div>' } },
]

function mountRegister() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(Register, {
    global: {
      plugins: [pinia, router],
      stubs: { ValidationErrors: true },
    },
  })
}

describe('Register', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders the Register form', () => {
    const wrapper = mountRegister()
    expect(wrapper.find('h2').text()).toBe('Register')
    expect(wrapper.text()).toContain('Register your account to start shopping')
  })

  it('renders name, email, password and confirm password inputs', () => {
    const wrapper = mountRegister()
    expect(wrapper.find('#name').exists()).toBe(true)
    expect(wrapper.find('#email').exists()).toBe(true)
    expect(wrapper.find('#password').exists()).toBe(true)
    expect(wrapper.find('#confirm_password').exists()).toBe(true)
  })

  it('toggles password visibility when eye icon clicked', async () => {
    const wrapper = mountRegister()
    const passwordInput = wrapper.find('#password')
    const toggleButtons = wrapper.findAll('button.btn-link')

    expect(passwordInput.attributes('type')).toBe('password')
    await toggleButtons[0].trigger('click')
    expect(passwordInput.attributes('type')).toBe('text')
  })

  it('calls authStore.register on form submit', async () => {
    const wrapper = mountRegister()
    const authStore = useAuthStore()
    vi.spyOn(authStore, 'register').mockResolvedValue({})

    await wrapper.find('#name').setValue('John Doe')
    await wrapper.find('#email').setValue('john@example.com')
    await wrapper.find('#password').setValue('password123')
    await wrapper.find('#confirm_password').setValue('password123')
    await wrapper.find('form').trigger('submit.prevent')

    expect(authStore.register).toHaveBeenCalledWith({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'password123',
      confirm_password: 'password123',
    })
  })
})
