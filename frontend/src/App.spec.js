import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useAuthStore } from './stores/useAuthStore'
import { useCartStore } from './stores/useCartStore'
import App from './App.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
]

function mountApp() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  return mount(App, {
    global: {
      plugins: [pinia, router],
      stubs: {
        Navbar: { template: '<nav data-test="navbar"></nav>' },
        Footer: { template: '<footer data-test="footer"></footer>' },
      },
    },
  })
}

describe('App', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders the App with container', () => {
    const wrapper = mountApp()
    expect(wrapper.find('.container').exists()).toBe(true)
  })

  it('renders Navbar component', () => {
    const wrapper = mountApp()
    expect(wrapper.find('[data-test="navbar"]').exists()).toBe(true)
  })

  it('renders Footer component', () => {
    const wrapper = mountApp()
    expect(wrapper.find('[data-test="footer"]').exists()).toBe(true)
  })

  it('calls fetchCart on mount when user is logged in', async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    const authStore = useAuthStore()
    const cartStore = useCartStore()
    authStore.isUserLoggedIn = true
    const fetchSpy = vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    const router = createRouter({
      history: createMemoryHistory(),
      routes,
    })
    mount(App, {
      global: {
        plugins: [pinia, router],
        stubs: {
          Navbar: { template: '<nav data-test="navbar"></nav>' },
          Footer: { template: '<footer data-test="footer"></footer>' },
        },
      },
    })
    await new Promise(r => setTimeout(r, 50))
    expect(fetchSpy).toHaveBeenCalled()
  })
})
