import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useCartStore } from './useCartStore'

vi.mock('vue-toastification', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  }),
}))

describe('useCartStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
    delete window.location
    window.location = { href: '' }
  })

  describe('initial state', () => {
    it('has correct initial state', () => {
      const store = useCartStore()
      expect(store.cartItems).toEqual([])
      expect(store.isLoading).toBe(false)
      expect(store.errorMessage).toBe('')
      expect(store.validCoupon).toEqual({
        coupon_id: null,
        name: null,
        discount: null,
        valid_until: null,
      })
      expect(store.uniqueHash).toBe(null)
    })
  })

  describe('getters', () => {
    it('getCartItems returns cartItems', () => {
      const store = useCartStore()
      const items = [{ id: 1, product: { name: 'Product' } }]
      store.cartItems = items
      expect(store.getCartItems).toEqual(items)
    })

    it('getIsLoading returns isLoading', () => {
      const store = useCartStore()
      store.isLoading = true
      expect(store.getIsLoading).toBe(true)
    })

    it('getErrorMessage returns errorMessage', () => {
      const store = useCartStore()
      store.errorMessage = 'Error'
      expect(store.getErrorMessage).toBe('Error')
    })
  })

  describe('transformCartItem', () => {
    it('transforms backend item to frontend format', () => {
      const store = useCartStore()
      const backendItem = {
        id: 5,
        reference: 'ref-123',
        product: { id: 1, name: 'Shirt' },
        quantity: 2,
        color: { id: 1 },
        size: { id: 2 },
      }
      const result = store.transformCartItem(backendItem)
      expect(result).toEqual({
        id: 5,
        reference: 'ref-123',
        product: { id: 1, name: 'Shirt' },
        qty: 2,
        color: { id: 1 },
        size: { id: 2 },
      })
    })
  })

  describe('fetchCart', () => {
    it('fetches and transforms cart items on success', async () => {
      const store = useCartStore()
      const backendItems = [
        {
          id: 1,
          reference: 'ref-1',
          product: { id: 1 },
          quantity: 2,
          color: {},
          size: {},
        },
      ]
      vi.mocked(axios.get).mockResolvedValue({
        data: {
          status: 200,
          data: { cart_items: backendItems },
        },
      })

      const result = await store.fetchCart()

      expect(axios.get).toHaveBeenCalledWith('/cart')
      expect(store.cartItems).toHaveLength(1)
      expect(store.cartItems[0].id).toBe(1)
      expect(store.cartItems[0].qty).toBe(2)
      expect(store.isLoading).toBe(false)
      expect(result.status).toBe(200)
    })

    it('sets empty cart when no items in response', async () => {
      const store = useCartStore()
      vi.mocked(axios.get).mockResolvedValue({
        data: { status: 200, data: {} },
      })

      await store.fetchCart()

      expect(store.cartItems).toEqual([])
    })

    it('handles 401 by redirecting to login', async () => {
      const store = useCartStore()
      vi.mocked(axios.get).mockRejectedValue({ response: { status: 401 } })

      await store.fetchCart()

      expect(window.location.href).toBe('/login')
    })
  })

  describe('addToCart', () => {
    it('adds item and fetches cart on success', async () => {
      const store = useCartStore()
      const item = {
        product: { id: 1 },
        color: { id: 1 },
        size: { id: 1 },
        qty: 2,
      }
      vi.mocked(axios.post).mockResolvedValue({
        data: { status: 201, message: 'Added' },
      })
      vi.spyOn(store, 'fetchCart').mockResolvedValue({})

      const result = await store.addToCart(item)

      expect(axios.post).toHaveBeenCalledWith('/cart', {
        product_id: 1,
        color_id: 1,
        size_id: 1,
        quantity: 2,
      })
      expect(store.fetchCart).toHaveBeenCalled()
      expect(result.status).toBe(201)
    })

    it('handles 401 by redirecting to login', async () => {
      const store = useCartStore()
      vi.mocked(axios.post).mockRejectedValue({ response: { status: 401 } })
      const item = { product: { id: 1 }, color: { id: 1 }, size: { id: 1 }, qty: 1 }

      await store.addToCart(item)

      expect(window.location.href).toBe('/login')
    })
  })

  describe('increaseQuantity', () => {
    it('increases quantity and fetches cart on success', async () => {
      const store = useCartStore()
      const item = {
        id: 1,
        qty: 2,
        product: { id: 1, qty: 10 },
      }
      vi.mocked(axios.put).mockResolvedValue({ data: { status: 200 } })
      vi.spyOn(store, 'fetchCart').mockResolvedValue({})

      const result = await store.increaseQuantity(item)

      expect(axios.put).toHaveBeenCalledWith('/cart/1', { quantity: 3 })
      expect(store.fetchCart).toHaveBeenCalled()
      expect(result.status).toBe(200)
    })

    it('does not call API when item has no id', async () => {
      const store = useCartStore()
      const item = { qty: 2, product: { qty: 10 } }

      await store.increaseQuantity(item)

      expect(axios.put).not.toHaveBeenCalled()
    })

    it('does not increase when at max quantity', async () => {
      const store = useCartStore()
      const item = {
        id: 1,
        qty: 5,
        product: { id: 1, qty: 5 },
      }

      await store.increaseQuantity(item)

      expect(axios.put).not.toHaveBeenCalled()
    })
  })

  describe('decreaseQuantity', () => {
    it('decreases quantity on success', async () => {
      const store = useCartStore()
      const item = {
        id: 1,
        qty: 3,
        product: { id: 1 },
      }
      vi.mocked(axios.put).mockResolvedValue({ data: { status: 200 } })
      vi.spyOn(store, 'fetchCart').mockResolvedValue({})

      await store.decreaseQuantity(item)

      expect(axios.put).toHaveBeenCalledWith('/cart/1', { quantity: 2 })
    })

    it('calls removeItem when qty is 1', async () => {
      const store = useCartStore()
      const item = { id: 1, qty: 1, product: { id: 1 } }
      vi.spyOn(store, 'removeItem').mockResolvedValue({})

      await store.decreaseQuantity(item)

      expect(store.removeItem).toHaveBeenCalledWith(item)
      expect(axios.put).not.toHaveBeenCalled()
    })
  })

  describe('removeItem', () => {
    it('removes item and fetches cart on success', async () => {
      const store = useCartStore()
      const item = { id: 1 }
      vi.mocked(axios.delete).mockResolvedValue({
        data: { status: 200, message: 'Removed' },
      })
      vi.spyOn(store, 'fetchCart').mockResolvedValue({})

      const result = await store.removeItem(item)

      expect(axios.delete).toHaveBeenCalledWith('/cart/1')
      expect(store.fetchCart).toHaveBeenCalled()
      expect(result.status).toBe(200)
    })

    it('does not call API when item has no id', async () => {
      const store = useCartStore()
      await store.removeItem({})
      expect(axios.delete).not.toHaveBeenCalled()
    })
  })

  describe('clearCart', () => {
    it('clears cartItems', () => {
      const store = useCartStore()
      store.cartItems = [{ id: 1 }]
      store.clearCart(false)
      expect(store.cartItems).toEqual([])
    })

    it('shows toast by default', () => {
      const store = useCartStore()
      store.clearCart()
      // Toast is mocked; just ensure no error
      expect(store.cartItems).toEqual([])
    })
  })

  describe('setValidCoupon', () => {
    it('sets validCoupon', () => {
      const store = useCartStore()
      const coupon = { coupon_id: 1, name: 'SAVE10', discount: 10 }
      store.setValidCoupon(coupon)
      expect(store.validCoupon).toEqual(coupon)
    })
  })

  describe('addCouponToCartItem', () => {
    it('adds coupon_id to all cart items', () => {
      const store = useCartStore()
      store.cartItems = [{ id: 1 }, { id: 2 }]
      store.addCouponToCartItem(5)
      expect(store.cartItems[0].coupon_id).toBe(5)
      expect(store.cartItems[1].coupon_id).toBe(5)
    })
  })

  describe('removeCouponFromAllItems', () => {
    it('removes coupon from all items and clears validCoupon', () => {
      const store = useCartStore()
      store.cartItems = [{ id: 1, coupon_id: 5 }]
      store.validCoupon = { coupon_id: 5, name: 'SAVE10' }
      store.removeCouponFromAllItems()
      expect(store.cartItems[0].coupon_id).toBe(null)
      expect(store.validCoupon).toEqual({
        coupon_id: null,
        name: null,
        discount: null,
        valid_until: null,
      })
    })
  })

  describe('setUniqueHash', () => {
    it('sets uniqueHash', () => {
      const store = useCartStore()
      store.setUniqueHash('abc123')
      expect(store.uniqueHash).toBe('abc123')
    })
  })
})
