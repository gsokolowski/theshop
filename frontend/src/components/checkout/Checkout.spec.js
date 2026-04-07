import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useCartStore } from '../../stores/useCartStore'
import { useAuthStore } from '../../stores/useAuthStore'
import Checkout from './Checkout.vue'

const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/checkout', component: { template: '<div>Checkout</div>' } },
]

const mockCartItem = {
  id: 1,
  reference: 'ref-1',
  product: { id: 1, name: 'Test Product', price: 29.99, thumbnail: '/img.jpg' },
  qty: 2,
  color: { id: 1, name: 'Red' },
  size: { id: 1, name: 'M' },
}

function mountCheckout(options = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const authStore = useAuthStore()
  authStore.user = options.user ?? { id: 0, profile_completed: false }
  authStore.isUserLoggedIn = options.isUserLoggedIn ?? false
  const cartStore = useCartStore()
  cartStore.cartItems = options.cartItems ?? []
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(Checkout, {
    global: {
      plugins: [pinia, router],
      stubs: {
        ProfileUpdate: { template: '<div data-test="profile-update"></div>' },
        Coupon: { template: '<div data-test="coupon"></div>' },
        Stripe: { template: '<button data-test="stripe-btn">Proceed to payment</button>' },
      },
    },
  })
}

describe('Checkout', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders the checkout container', () => {
    const wrapper = mountCheckout()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    expect(wrapper.find('.row').exists()).toBe(true)
  })

  it('renders ProfileUpdate component', () => {
    const wrapper = mountCheckout()
    expect(wrapper.find('[data-test="profile-update"]').exists()).toBe(true)
  })

  it('renders Coupon component', () => {
    const wrapper = mountCheckout()
    expect(wrapper.find('[data-test="coupon"]').exists()).toBe(true)
  })

  it('displays cart items in checkout', async () => {
    const wrapper = mountCheckout()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Test Product')
    expect(wrapper.text()).toContain('Red')
    expect(wrapper.text()).toContain('M')
    expect(wrapper.text()).toContain('29.99')
  })

  it('displays Total in checkout', async () => {
    const wrapper = mountCheckout()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Total')
    const total = (29.99 * 2).toFixed(2)
    expect(wrapper.text()).toContain(`$${total}`)
  })

  it('shows Stripe component when user has completed profile and cart has items', async () => {
    const wrapper = mountCheckout()
    const cartStore = useCartStore()
    const authStore = useAuthStore()
    cartStore.cartItems = [mockCartItem]
    authStore.user = { id: 1, profile_completed: true }
    authStore.isUserLoggedIn = true
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[data-test="stripe-btn"]').exists()).toBe(true)
  })

  it('hides Stripe when cart is empty', async () => {
    const wrapper = mountCheckout()
    const cartStore = useCartStore()
    const authStore = useAuthStore()
    cartStore.cartItems = []
    authStore.user = { id: 1, profile_completed: true }
    authStore.isUserLoggedIn = true
    await wrapper.vm.$nextTick()
    const stripeBtn = wrapper.find('[data-test="stripe-btn"]')
    expect(stripeBtn.exists()).toBe(false)
  })

  it('hides Stripe when user is null without throwing', async () => {
    const wrapper = mountCheckout({ user: null, cartItems: [mockCartItem] })
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[data-test="stripe-btn"]').exists()).toBe(false)
  })

  it('redirects to home when cart is empty on mount', async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    useAuthStore().user = { profile_completed: false }
    useCartStore().cartItems = []
    const router = createRouter({ history: createMemoryHistory(), routes })
    const pushSpy = vi.spyOn(router, 'push')
    mount(Checkout, {
      global: {
        plugins: [pinia, router],
        stubs: {
          ProfileUpdate: { template: '<div data-test="profile-update"></div>' },
          Coupon: { template: '<div data-test="coupon"></div>' },
          Stripe: { template: '<button data-test="stripe-btn">Proceed to payment</button>' },
        },
      },
    })
    await new Promise(r => setTimeout(r, 50))
    expect(pushSpy).toHaveBeenCalledWith('/')
  })
})
