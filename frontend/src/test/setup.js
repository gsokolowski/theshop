import { vi } from 'vitest'

// Mock axios globally to prevent network requests in tests.
// Individual test files can override with vi.mocked(axios.get).mockResolvedValue(...)
vi.mock('axios', () => ({
  default: {
    get: vi.fn().mockImplementation((url) => {
      if (typeof url === 'string' && url.includes('/api/cart')) {
        return Promise.resolve({ data: { status: 200, data: { cart_items: [] } } })
      }
      if (typeof url === 'string' && url.includes('/api/products')) {
        return Promise.resolve({ data: { status: 200, data: { products: [] } } })
      }
      if (typeof url === 'string' && url.includes('/api/wishlist')) {
        return Promise.resolve({ data: { status: 200, data: { wishlist_items: [] } } })
      }
      if (typeof url === 'string' && url.includes('/api/user')) {
        return Promise.resolve({ data: { status: 200, data: null } })
      }
      if (typeof url === 'string' && url.includes('/api/orders')) {
        return Promise.resolve({ data: { status: 200, data: { orders: [] } } })
      }
      if (typeof url === 'string' && url.includes('/api/coupon')) {
        return Promise.resolve({ data: { status: 404 } })
      }
      return Promise.resolve({ data: { status: 200, data: {} } })
    }),
    post: vi.fn().mockResolvedValue({ data: { status: 200 } }),
    put: vi.fn().mockResolvedValue({ data: { status: 200 } }),
    delete: vi.fn().mockResolvedValue({ data: { status: 200 } }),
  },
}))
