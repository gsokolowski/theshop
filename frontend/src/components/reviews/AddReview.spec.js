import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import axios from 'axios'
import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
import { useAuthStore } from '../../stores/useAuthStore'
import AddReview from './AddReview.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/login', component: { template: '<div>Login</div>' } },
]

function mountAddReview() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(AddReview, {
    global: {
      plugins: [pinia, router],
      stubs: {
        Spinner: { template: '<div data-test="spinner"></div>' },
        StarRating: { template: '<div data-test="star-rating"></div>' },
      },
    },
  })
}

describe('AddReview', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders the AddReview card', () => {
    const wrapper = mountAddReview()
    expect(wrapper.find('.card').exists()).toBe(true)
    expect(wrapper.text()).toContain('Add your review')
  })

  it('displays message when user has not purchased the product', async () => {
    const wrapper = mountAddReview()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { id: 1, name: 'Test Product' }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1, orders: [] }
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('You must purchase this product before you can leave a review')
  })

  it('renders form when user has purchased (mock hasExistingReview false)', async () => {
    vi.spyOn(axios, 'get').mockResolvedValue({ data: { data: { has_review: false } } })
    const wrapper = mountAddReview()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { id: 1 }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1, orders: [{ products: [{ id: 1 }] }] }
    await wrapper.vm.$nextTick()
    await new Promise(r => setTimeout(r, 50))
    const form = wrapper.find('form')
    expect(form.exists()).toBe(true)
  })

  it('has title and body inputs when form is shown', async () => {
    vi.spyOn(axios, 'get').mockResolvedValue({ data: { data: { has_review: false } } })
    const wrapper = mountAddReview()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { id: 1 }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1, orders: [{ products: [{ id: 1 }] }] }
    await wrapper.vm.$nextTick()
    await new Promise(r => setTimeout(r, 100))
    expect(wrapper.find('#title').exists()).toBe(true)
    expect(wrapper.find('#body').exists()).toBe(true)
  })

  it('has Add Review submit button when form is shown', async () => {
    vi.spyOn(axios, 'get').mockResolvedValue({ data: { data: { has_review: false } } })
    const wrapper = mountAddReview()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { id: 1 }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1, orders: [{ products: [{ id: 1 }] }] }
    await wrapper.vm.$nextTick()
    await new Promise(r => setTimeout(r, 100))
    const submitBtn = wrapper.find('button[type="submit"]')
    expect(submitBtn.exists()).toBe(true)
    expect(submitBtn.text()).toContain('Add Review')
  })

  it('form submit triggers validation', async () => {
    vi.spyOn(axios, 'get').mockResolvedValue({ data: { data: { has_review: false } } })
    const wrapper = mountAddReview()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { id: 1 }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1, orders: [{ products: [{ id: 1 }] }] }
    const addReviewSpy = vi.spyOn(productStore, 'addReview').mockResolvedValue({ data: { message: 'Success' } })
    await wrapper.vm.$nextTick()
    await new Promise(r => setTimeout(r, 100))
    await wrapper.find('form').trigger('submit.prevent')
    expect(addReviewSpy).not.toHaveBeenCalled()
  })
})
