import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useCartStore } from '../../stores/useCartStore'
import { useAuthStore } from '../../stores/useAuthStore'
import Coupon from './Coupon.vue'

function mountCoupon() {
  const pinia = createPinia()
  setActivePinia(pinia)
  return mount(Coupon, {
    global: {
      plugins: [pinia],
    },
  })
}

describe('Coupon', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders the Coupon section', () => {
    const wrapper = mountCoupon()
    expect(wrapper.text()).toContain('Coupon')
    expect(wrapper.find('input[placeholder="Coupon Name"]').exists()).toBe(true)
    expect(wrapper.find('button').text()).toContain('Apply')
  })

  it('Apply button is disabled when input is empty', () => {
    const wrapper = mountCoupon()
    const applyBtn = wrapper.find('button.btn-primary')
    expect(applyBtn.attributes('disabled')).toBeDefined()
  })

  it('calls axios and applyCoupon when valid coupon entered', async () => {
    const wrapper = mountCoupon()
    const cartStore = useCartStore()
    const authStore = useAuthStore()
    authStore.accessToken = 'test-token'
    vi.spyOn(axios, 'get').mockResolvedValue({
      status: 200,
      data: {
        data: { id: 1, name: 'SAVE10', discount: 10, valid_until: '2025-12-31' },
        message: 'Coupon applied',
      },
    })
    const setValidCouponSpy = vi.spyOn(cartStore, 'setValidCoupon')
    const addCouponSpy = vi.spyOn(cartStore, 'addCouponToCartItem')
    await wrapper.find('input').setValue('SAVE10')
    await wrapper.find('button.btn-primary').trigger('click')
    expect(axios.get).toHaveBeenCalledWith(
      '/coupon/SAVE10',
      expect.objectContaining({
        headers: expect.objectContaining({
          Authorization: 'Bearer test-token',
        }),
      })
    )
    expect(setValidCouponSpy).toHaveBeenCalled()
    expect(addCouponSpy).toHaveBeenCalled()
  })

  it('shows Remove coupon button when valid coupon is applied', async () => {
    const wrapper = mountCoupon()
    const cartStore = useCartStore()
    cartStore.validCoupon = {
      coupon_id: 1,
      name: 'SAVE10',
      discount: 10,
      valid_until: '2025-12-31',
    }
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('SAVE10')
    const removeBtn = wrapper.find('button.btn-outline-danger')
    expect(removeBtn.exists()).toBe(true)
  })

  it('calls removeCouponFromAllItems when Remove coupon is clicked', async () => {
    const wrapper = mountCoupon()
    const cartStore = useCartStore()
    cartStore.validCoupon = {
      coupon_id: 1,
      name: 'SAVE10',
      discount: 10,
      valid_until: '2025-12-31',
    }
    const removeSpy = vi.spyOn(cartStore, 'removeCouponFromAllItems')
    await wrapper.vm.$nextTick()
    await wrapper.find('button.btn-outline-danger').trigger('click')
    expect(removeSpy).toHaveBeenCalled()
  })

  it('Apply coupon on Enter key', async () => {
    const wrapper = mountCoupon()
    const authStore = useAuthStore()
    authStore.accessToken = 'test-token'
    vi.spyOn(axios, 'get').mockResolvedValue({
      status: 200,
      data: {
        data: { id: 1, name: 'SAVE10', discount: 10, valid_until: '2025-12-31' },
        message: 'Coupon applied',
      },
    })
    await wrapper.find('input').setValue('SAVE10')
    await wrapper.find('input').trigger('keyup.enter')
    expect(axios.get).toHaveBeenCalled()
  })
})
