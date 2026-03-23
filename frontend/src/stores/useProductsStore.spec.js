import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useProductsStore } from './useProductsStore'

describe('useProductsStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  describe('initial state', () => {
    it('has correct initial state', () => {
      const store = useProductsStore()
      expect(store.products).toEqual([])
      expect(store.categories).toEqual([])
      expect(store.brands).toEqual([])
      expect(store.colors).toEqual([])
      expect(store.sizes).toEqual([])
      expect(store.isLoading).toBe(false)
      expect(store.filter).toBe(null)
      expect(store.productsPerPage).toBe(4)
      expect(store.productCount).toBe(10)
      expect(store.currentPage).toBe(1)
      expect(store.lastFetch).toBe(null)
      expect(store.searchTerm).toBe('')
    })
  })

  describe('getters', () => {
    it('getProducts returns products', () => {
      const store = useProductsStore()
      const products = [{ id: 1, name: 'Product' }]
      store.products = products
      expect(store.getProducts).toEqual(products)
    })

    it('getCategories returns categories', () => {
      const store = useProductsStore()
      const categories = [{ id: 1, name: 'Category' }]
      store.categories = categories
      expect(store.getCategories).toEqual(categories)
    })

    it('getBrands returns brands', () => {
      const store = useProductsStore()
      const brands = [{ id: 1, name: 'Brand' }]
      store.brands = brands
      expect(store.getBrands).toEqual(brands)
    })

    it('getColors returns colors', () => {
      const store = useProductsStore()
      const colors = [{ id: 1, name: 'Red' }]
      store.colors = colors
      expect(store.getColors).toEqual(colors)
    })

    it('getSizes returns sizes', () => {
      const store = useProductsStore()
      const sizes = [{ id: 1, name: 'M' }]
      store.sizes = sizes
      expect(store.getSizes).toEqual(sizes)
    })

    it('getInStockProducts filters products with status 1', () => {
      const store = useProductsStore()
      store.products = [
        { id: 1, status: 1, name: 'In stock' },
        { id: 2, status: 0, name: 'Out of stock' },
        { id: 3, status: 1, name: 'In stock 2' },
      ]
      const inStock = store.getInStockProducts
      expect(inStock).toHaveLength(2)
      expect(inStock.map(p => p.id)).toEqual([1, 3])
    })

    it('getProductCount returns productCount', () => {
      const store = useProductsStore()
      store.productCount = 25
      expect(store.getProductCount).toBe(25)
    })
  })

  describe('resetProductsPerPage', () => {
    it('resets productsPerPage and currentPage', () => {
      const store = useProductsStore()
      store.productsPerPage = 12
      store.currentPage = 3
      store.resetProductsPerPage()
      expect(store.productsPerPage).toBe(4)
      expect(store.currentPage).toBe(1)
    })
  })

  describe('loadMoreProducts', () => {
    it('fetches next page and appends to products when lastFetch exists', async () => {
      const store = useProductsStore()
      store.products = [{ id: 1 }]
      store.productCount = 10
      store.lastFetch = { url: '/products', params: {} }
      store.currentPage = 1
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: [{ id: 2 }, { id: 3 }, { id: 4 }], meta: { total: 10 } },
      })

      await store.loadMoreProducts()

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { page: 2, per_page: 4 },
      })
      expect(store.products).toHaveLength(4)
      expect(store.products.map(p => p.id)).toEqual([1, 2, 3, 4])
      expect(store.currentPage).toBe(2)
    })

    it('does nothing when lastFetch is null', async () => {
      const store = useProductsStore()
      store.products = [{ id: 1 }]
      store.lastFetch = null

      await store.loadMoreProducts()

      expect(axios.get).not.toHaveBeenCalled()
    })
  })

  describe('fetchAllProducts', () => {
    it('fetches products and sets state on success', async () => {
      const store = useProductsStore()
      const products = [{ id: 1, name: 'Product', status: 1 }]
      const categories = [{ id: 1, name: 'Category' }]
      const brands = [{ id: 1, name: 'Brand' }]
      const colors = [{ id: 1 }]
      const sizes = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: {
          data: products,
          meta: { total: 50 },
          categories,
          brands,
          colors,
          sizes,
        },
      })

      await store.fetchAllProducts()

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
      expect(store.categories).toEqual(categories)
      expect(store.brands).toEqual(brands)
      expect(store.colors).toEqual(colors)
      expect(store.sizes).toEqual(sizes)
      expect(store.productCount).toBe(50)
      expect(store.lastFetch).toEqual({ url: '/products', params: {} })
      expect(store.isLoading).toBe(false)
    })
  })

  describe('filterProductsByCategory', () => {
    it('fetches products by category slug', async () => {
      const store = useProductsStore()
      const products = [{ id: 1, name: 'Shirt' }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsByCategory('shirts')

      expect(axios.get).toHaveBeenCalledWith('/products/category/shirts', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
      expect(store.productCount).toBe(1)
      expect(store.lastFetch).toEqual({ url: '/products/category/shirts', params: {} })
    })
  })

  describe('filterProductsByBrand', () => {
    it('fetches products by brand slug', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsByBrand('nike')

      expect(axios.get).toHaveBeenCalledWith('/products/brand/nike', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
    })
  })

  describe('filterProductsBySize', () => {
    it('fetches products by size id', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsBySize(2)

      expect(axios.get).toHaveBeenCalledWith('/products/size/2', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
    })
  })

  describe('filterProductsByColor', () => {
    it('fetches products by color id', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsByColor(3)

      expect(axios.get).toHaveBeenCalledWith('/products/color/3', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
    })
  })

  describe('filterProductsBySearchTerm', () => {
    it('fetches products by search term', async () => {
      const store = useProductsStore()
      store.searchTerm = 'shoes'
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsBySearchTerm()

      expect(axios.get).toHaveBeenCalledWith('/products/search/shoes', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
    })
  })

  describe('clearFilters', () => {
    it('resets state and fetches all products', async () => {
      const store = useProductsStore()
      store.products = [{ id: 1 }]
      store.filter = 'brand'
      store.productsPerPage = 12
      store.currentPage = 3
      const products = [{ id: 1, status: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: {
          data: products,
          meta: { total: 1 },
          categories: [],
          brands: [],
          colors: [],
          sizes: [],
        },
      })

      await store.clearFilters()

      expect(store.filter).toBe(null)
      expect(store.productsPerPage).toBe(4)
      expect(store.currentPage).toBe(1)
      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
    })
  })
})
