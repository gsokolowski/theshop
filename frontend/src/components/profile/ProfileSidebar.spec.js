import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useAuthStore } from '../../stores/useAuthStore'
import ProfileSidebar from './ProfileSidebar.vue'

const routes = [
  { path: '/', component: { template: '<div></div>' } },
  { path: '/profile', component: { template: '<div>Profile</div>' } },
  { path: '/user/orders', component: { template: '<div>Orders</div>' } },
  { path: '/user/wishlist', component: { template: '<div>Wishlist</div>' } },
  { path: '/:pathMatch(.*)*', component: { template: '<div></div>' } },
]

function mountProfileSidebar(user = null) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const authStore = useAuthStore()
  if (user) {
    authStore.setUser(user)
    authStore.setUserLoggedIn(true)
  }
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(ProfileSidebar, {
    global: {
      plugins: [pinia, router],
      stubs: { Spinner: true },
    },
  })
}

describe('ProfileSidebar', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the sidebar', () => {
    const wrapper = mountProfileSidebar({ name: 'John', email: 'john@test.com' })
    expect(wrapper.find('.col-md-4').exists()).toBe(true)
  })

  it('displays user name when logged in', () => {
    const wrapper = mountProfileSidebar({ name: 'John Doe', email: 'john@test.com' })
    expect(wrapper.text()).toContain('John Doe')
  })

  it('displays user email', () => {
    const wrapper = mountProfileSidebar({ name: 'John', email: 'john@test.com' })
    expect(wrapper.text()).toContain('john@test.com')
  })

  it('has Orders link', () => {
    const wrapper = mountProfileSidebar({ name: 'John', email: 'john@test.com' })
    expect(wrapper.text()).toContain('Orders')
  })

  it('has Wishlist link', () => {
    const wrapper = mountProfileSidebar({ name: 'John', email: 'john@test.com' })
    expect(wrapper.text()).toContain('Wishlist')
  })
})
