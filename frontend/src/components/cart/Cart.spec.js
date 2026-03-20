import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import axios from 'axios'
import { useCartStore } from '../../stores/useCartStore'
import Cart from './Cart.vue'

vi.mock('axios')
vi.mock('vue-toastification', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  }),
}))

const routes = [
  { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
  { path: '/cart', component: { template: '<div>Cart</div>' } },
  { path: '/checkout', component: { template: '<div>Checkout</div>' } },
]

function mountCart(options = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const cartStore = useCartStore()
  vi.spyOn(cartStore, 'fetchCart').mockResolvedValue(options.fetchCartResponse ?? {})
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(Cart, {
    global: {
      plugins: [pinia, router],
    },
  })
}

const mockCartItem = {
  id: 1,
  reference: 'ref-1',
  product: { id: 1, name: 'Test Product', price: 29.99, thumbnail: '/img.jpg', qty: 5 },
  qty: 2,
  color: { id: 1, name: '#000000' },
  size: { id: 1, name: 'M' },
}

const mockCartResponse = { data: { status: 200, data: { cart_items: [] } } }

describe('Cart', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(axios.get).mockResolvedValue(mockCartResponse)
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders the cart container', () => {
    const wrapper = mountCart()
    expect(wrapper.find('.card').exists()).toBe(true)
  })

  it('displays empty cart message when cart has no items', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = []
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Your cart is empty.')
    expect(wrapper.find('button.btn-primary').text()).toContain('Continue Shopping')
  })

  it('displays cart table when cart has items', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    await wrapper.vm.$nextTick()
    expect(wrapper.find('table.table').exists()).toBe(true)
    expect(wrapper.text()).toContain('Cart')
    expect(wrapper.text()).toContain('Test Product')
    expect(wrapper.text()).toContain('29.99')
    expect(wrapper.text()).toContain('M')
  })

  it('calls fetchCart on mount', async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    const cartStore = useCartStore()
    const fetchSpy = vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    const router = createRouter({ history: createMemoryHistory(), routes })
    mount(Cart, { global: { plugins: [pinia, router] } })
    await new Promise(r => setTimeout(r, 50))
    expect(fetchSpy).toHaveBeenCalled()
  })

  it('navigates to checkout when Checkout button is clicked', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    const router = wrapper.vm.$router
    const pushSpy = vi.spyOn(router, 'push')
    await wrapper.vm.$nextTick()
    const checkoutBtn = wrapper.find('button.btn-primary')
    await checkoutBtn.trigger('click')
    expect(pushSpy).toHaveBeenCalledWith('/checkout')
  })

  it('navigates to home when Continue Shopping is clicked', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    const router = wrapper.vm.$router
    const pushSpy = vi.spyOn(router, 'push')
    await wrapper.vm.$nextTick()
    const continueBtn = wrapper.findAll('button').find(b => b.text().includes('Continue Shopping'))
    if (continueBtn) {
      await continueBtn.trigger('click')
      expect(pushSpy).toHaveBeenCalledWith('/')
    }
  })

  it('calls increaseQuantity when caret-up is clicked', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    vi.spyOn(cartStore, 'increaseQuantity').mockResolvedValue({})
    await wrapper.vm.$nextTick()
    const caretUp = wrapper.find('.bi-caret-up')
    await caretUp.trigger('click')
    expect(cartStore.increaseQuantity).toHaveBeenCalledWith(mockCartItem)
  })

  it('calls decreaseQuantity when caret-down is clicked', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    vi.spyOn(cartStore, 'decreaseQuantity').mockResolvedValue({})
    await wrapper.vm.$nextTick()
    const caretDown = wrapper.find('.bi-caret-down')
    await caretDown.trigger('click')
    expect(cartStore.decreaseQuantity).toHaveBeenCalledWith(mockCartItem)
  })

  it('calls removeItem when remove button is clicked', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    vi.spyOn(cartStore, 'removeItem').mockResolvedValue({})
    await wrapper.vm.$nextTick()
    const removeBtn = wrapper.find('button[title="Remove Item"]')
    await removeBtn.trigger('click')
    expect(cartStore.removeItem).toHaveBeenCalledWith(mockCartItem)
  })

  it('displays correct subtotal and total', async () => {
    const wrapper = mountCart()
    const cartStore = useCartStore()
    cartStore.cartItems = [mockCartItem]
    vi.spyOn(cartStore, 'fetchCart').mockResolvedValue({})
    await wrapper.vm.$nextTick()
    const subtotal = (29.99 * 2).toFixed(2)
    expect(wrapper.text()).toContain(`$${subtotal}`)
    expect(wrapper.text()).toContain('Total:')
  })
})
