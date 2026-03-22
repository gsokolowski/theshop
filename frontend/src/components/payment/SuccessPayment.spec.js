import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import axios from 'axios'
import { useCartStore } from '../../stores/useCartStore'
import { useAuthStore } from '../../stores/useAuthStore'
import SuccessPayment from './SuccessPayment.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/success/payment/:hash', component: { template: '<div>Success</div>' }},
]

const mockCartItem = {
  id: 1,
  product: { id: 1, price: 29.99 },
  qty: 2,
  color: { id: 1 },
  size: { id: 1 },
  coupon_id: null,
}

async function mountSuccessPayment(options = {}) {
  const hash = options.hash || 'test-hash-123'
  const cartItems = options.cartItems || []
  const uniqueHash = options.uniqueHash !== undefined ? options.uniqueHash : hash
  const accessToken = options.accessToken || 'test-token'

  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  await router.push('/success/payment/' + hash)
  const cartStore = useCartStore()
  const authStore = useAuthStore()
  cartStore.cartItems = cartItems
  cartStore.uniqueHash = uniqueHash
  authStore.accessToken = accessToken
  return mount(SuccessPayment, {
    global: {
      plugins: [pinia, router],
    },
  })
}

describe('SuccessPayment', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders success message', async () => {
    const wrapper = await mountSuccessPayment()
    expect(wrapper.find('.card').exists()).toBe(true)
    expect(wrapper.text()).toContain('Payment is done successfully')
  })

  it('calls axios post to store orders when hash matches', async () => {
    const postSpy = vi.spyOn(axios, 'post').mockResolvedValue({
      data: { data: {} },
    })
    await mountSuccessPayment({
      hash: 'matching-hash',
      cartItems: [mockCartItem],
      uniqueHash: 'matching-hash',
    })
    await new Promise(resolve => setTimeout(resolve, 100))
    expect(postSpy).toHaveBeenCalledWith(
      '/orders',
      expect.objectContaining({
        cartItems: expect.any(Array),
      }),
      expect.any(Object)
    )
  })

  it('redirects to home when hash does not match', async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    const cartStore = useCartStore()
    cartStore.uniqueHash = 'correct-hash'
    useAuthStore().accessToken = 'test-token'
    const router = createRouter({
      history: createMemoryHistory(),
      routes,
    })
    const pushSpy = vi.spyOn(router, 'push')
    await router.push('/success/payment/wrong-hash')
    mount(SuccessPayment, {
      global: { plugins: [pinia, router] },
    })
    await new Promise(r => setTimeout(r, 50))
    expect(pushSpy).toHaveBeenCalledWith('/')
  })

  it('clears cart after successful order storage', async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    cartStore.uniqueHash = 'match-hash'
    useAuthStore().accessToken = 'test-token'
    vi.spyOn(axios, 'post').mockResolvedValue({
      data: { data: {} },
    })
    const clearCartSpy = vi.spyOn(cartStore, 'clearCart')
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/success/payment/:hash', component: { template: '<div>Success</div>' } },
      ],
    })
    await router.push('/success/payment/match-hash')
    mount(SuccessPayment, {
      global: { plugins: [pinia, router] },
    })
    await new Promise(r => setTimeout(r, 100))
    expect(clearCartSpy).toHaveBeenCalled()
  })
})
