import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
import { useWishlistStore } from '../../stores/useWishlistStore'
import Product from './Product.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/product/:slug', name: 'product', component: { template: '<div>Product</div>' } },
]

function mountProduct(slug = 'test-product') {
  const pinia = createPinia()
  setActivePinia(pinia)
  const productDetailsStore = useProductDetailsStore()
  productDetailsStore.product = null
  vi.spyOn(productDetailsStore, 'fetchProduct').mockResolvedValue(undefined)
  const wishlistStore = useWishlistStore()
  vi.spyOn(wishlistStore, 'fetchWishlist').mockResolvedValue(undefined)
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  router.push({ name: 'product', params: { slug } })
  return mount(Product, {
    global: {
      plugins: [pinia, router],
      stubs: {
        Spinner: true,
        VueImageZoomer: true,
        ReviewList: true,
        AddReview: true,
        EditReview: true,
        StarRating: true,
      },
    },
  })
}

describe('Product', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders Back button', () => {
    const wrapper = mountProduct()
    expect(wrapper.text()).toContain('Back')
  })

  it('renders Spinner component', () => {
    const wrapper = mountProduct()
    expect(wrapper.findComponent({ name: 'Spinner' }).exists()).toBe(true)
  })
})
