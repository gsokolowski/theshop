import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useCartStore } from '../../stores/useCartStore'
import { useAuthStore } from '../../stores/useAuthStore'
import Stripe from './Stripe.vue'

function mountStripe() {
  const pinia = createPinia()
  setActivePinia(pinia)
  return mount(Stripe, {
    global: {
      plugins: [pinia],
    },
  })
}

const mockCartItem = {
  id: 1,
  product: { id: 1, price: 29.99, qty: 5 },
  qty: 2,
  coupon_id: null,
}

describe('Stripe', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders the Proceed to payment button', () => {
    const wrapper = mountStripe()
    expect(wrapper.find('button').exists()).toBe(true)
    expect(wrapper.text()).toContain('Proceed to payment')
  })

  it('calls axios post when button is clicked', async () => {
    const wrapper = mountStripe()
    const cartStore = useCartStore()
    const authStore = useAuthStore()
    cartStore.cartItems = [mockCartItem]
    authStore.accessToken = 'test-token'
    const postSpy = vi.spyOn(axios, 'post').mockResolvedValue({
      data: { data: { url: 'https://stripe.com/checkout' } },
    })
    await wrapper.find('button').trigger('click')
    expect(postSpy).toHaveBeenCalledWith(
      '/api/orders/pay',
      expect.objectContaining({
        cartItems: expect.any(Array),
        success_url: expect.any(String),
        cancel_url: expect.any(String),
      }),
      expect.objectContaining({
        headers: expect.objectContaining({
          Authorization: 'Bearer test-token',
        }),
      })
    )
  })

  it('redirects to payment URL on success', async () => {
    const wrapper = mountStripe()
    const cartStore = useCartStore()
    const authStore = useAuthStore()
    cartStore.cartItems = [mockCartItem]
    authStore.accessToken = 'test-token'
    const originalLocation = window.location
    delete window.location
    window.location = { href: '' }
    vi.spyOn(axios, 'post').mockResolvedValue({
      data: { data: { url: 'https://stripe.com/checkout' } },
    })
    await wrapper.find('button').trigger('click')
    expect(window.location.href).toBe('https://stripe.com/checkout')
    window.location = originalLocation
  })
})
