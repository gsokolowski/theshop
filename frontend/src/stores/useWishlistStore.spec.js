import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useWishlistStore } from './useWishlistStore'

vi.mock('vue-toastification', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  }),
}))

describe('useWishlistStore', () => {
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
      const store = useWishlistStore()
      expect(store.wishlistItems).toEqual([])
      expect(store.isLoading).toBe(false)
      expect(store.errorMessage).toBe('')
    })
  })

  describe('getters', () => {
    it('getWishlistItems returns wishlistItems', () => {
      const store = useWishlistStore()
      const items = [{ id: 1, product: { id: 1 } }]
      store.wishlistItems = items
      expect(store.getWishlistItems).toEqual(items)
    })

    it('getIsLoading returns isLoading', () => {
      const store = useWishlistStore()
      store.isLoading = true
      expect(store.getIsLoading).toBe(true)
    })

    it('getErrorMessage returns errorMessage', () => {
      const store = useWishlistStore()
      store.errorMessage = 'Error'
      expect(store.getErrorMessage).toBe('Error')
    })

    it('isProductInWishlist returns true when product exists', () => {
      const store = useWishlistStore()
      store.wishlistItems = [{ id: 1, product: { id: 10 } }]
      expect(store.isProductInWishlist(10)).toBe(true)
    })

    it('isProductInWishlist returns false when product not in list', () => {
      const store = useWishlistStore()
      store.wishlistItems = [{ id: 1, product: { id: 10 } }]
      expect(store.isProductInWishlist(99)).toBe(false)
    })
  })

  describe('fetchWishlist', () => {
    it('fetches and sets wishlist items on success', async () => {
      const store = useWishlistStore()
      const items = [{ id: 1, product: { id: 1, name: 'Product' } }]
      vi.mocked(axios.get).mockResolvedValue({
        data: {
          status: 200,
          data: { wishlist_items: items },
        },
      })

      const result = await store.fetchWishlist()

      expect(axios.get).toHaveBeenCalledWith('/wishlist')
      expect(store.wishlistItems).toEqual(items)
      expect(store.isLoading).toBe(false)
      expect(result.status).toBe(200)
    })

    it('sets empty array when no items in response', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.get).mockResolvedValue({
        data: { status: 200, data: {} },
      })

      await store.fetchWishlist()

      expect(store.wishlistItems).toEqual([])
    })

    it('handles 401 by redirecting to login', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.get).mockRejectedValue({ response: { status: 401 } })

      await store.fetchWishlist()

      expect(window.location.href).toBe('/login')
    })

    it('throws and sets errorMessage on other errors', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.get).mockRejectedValue({
        response: { data: { error: 'Server error' } },
      })

      await expect(store.fetchWishlist()).rejects.toBeDefined()

      expect(store.errorMessage).toBe('Server error')
      expect(store.isLoading).toBe(false)
    })
  })

  describe('addToWishlist', () => {
    it('adds product and fetches wishlist on success', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.post).mockResolvedValue({
        data: { status: 201, message: 'Added' },
      })
      vi.spyOn(store, 'fetchWishlist').mockResolvedValue({})

      const result = await store.addToWishlist(5)

      expect(axios.post).toHaveBeenCalledWith('/wishlist', { product_id: 5 })
      expect(store.fetchWishlist).toHaveBeenCalled()
      expect(result.status).toBe(201)
    })

    it('handles 401 by redirecting to login', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.post).mockRejectedValue({ response: { status: 401 } })

      await store.addToWishlist(1)

      expect(window.location.href).toBe('/login')
    })

    it('handles 400 duplicate without throwing', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.post).mockRejectedValue({
        response: { status: 400, data: { message: 'Already in wishlist' } },
      })

      await store.addToWishlist(1)

      expect(store.errorMessage).toBeTruthy()
      expect(store.isLoading).toBe(false)
    })
  })

  describe('removeFromWishlist', () => {
    it('removes item and fetches wishlist on success', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.delete).mockResolvedValue({
        data: { status: 200, message: 'Removed' },
      })
      vi.spyOn(store, 'fetchWishlist').mockResolvedValue({})

      const result = await store.removeFromWishlist(3)

      expect(axios.delete).toHaveBeenCalledWith('/wishlist/3')
      expect(store.fetchWishlist).toHaveBeenCalled()
      expect(result.status).toBe(200)
    })

    it('handles 401 by redirecting to login', async () => {
      const store = useWishlistStore()
      vi.mocked(axios.delete).mockRejectedValue({ response: { status: 401 } })

      await store.removeFromWishlist(1)

      expect(window.location.href).toBe('/login')
    })
  })

  describe('toggleWishlist', () => {
    it('removes from wishlist when product exists', async () => {
      const store = useWishlistStore()
      store.wishlistItems = [{ id: 10, product: { id: 5 } }]
      const removeSpy = vi.spyOn(store, 'removeFromWishlist').mockResolvedValue({})
      const addSpy = vi.spyOn(store, 'addToWishlist').mockResolvedValue({})

      await store.toggleWishlist(5)

      expect(removeSpy).toHaveBeenCalledWith(10)
      expect(addSpy).not.toHaveBeenCalled()
    })

    it('adds to wishlist when product not in list', async () => {
      const store = useWishlistStore()
      store.wishlistItems = []
      const addSpy = vi.spyOn(store, 'addToWishlist').mockResolvedValue({})

      await store.toggleWishlist(5)

      expect(addSpy).toHaveBeenCalledWith(5)
    })
  })
})
