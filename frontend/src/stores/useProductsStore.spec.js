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
      expect(store.filters).toEqual({
        categorySlug: null,
        brandSlug: null,
        colorId: null,
        sizeId: null,
      })
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

    it('merges stored filter params when loading more', async () => {
      const store = useProductsStore()
      store.products = [{ id: 1 }]
      store.productCount = 10
      store.lastFetch = {
        url: '/products',
        params: { category: 'shoes', brand: 'nike', color_id: 2, size_id: 3, search: 'x' },
      }
      store.currentPage = 1
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: [{ id: 2 }], meta: { total: 10 } },
      })

      await store.loadMoreProducts()

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: {
          category: 'shoes',
          brand: 'nike',
          color_id: 2,
          size_id: 3,
          search: 'x',
          page: 2,
          per_page: 4,
        },
      })
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
    it('clears facet state and search then fetches products', async () => {
      const store = useProductsStore()
      store.filters.categorySlug = 'old'
      store.searchTerm = 'findme'
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

      expect(store.filters).toEqual({
        categorySlug: null,
        brandSlug: null,
        colorId: null,
        sizeId: null,
      })
      expect(store.searchTerm).toBe('')
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
    it('fetches products with category query param', async () => {
      const store = useProductsStore()
      const products = [{ id: 1, name: 'Shirt' }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 }, categories: [], brands: [], colors: [], sizes: [] },
      })

      await store.filterProductsByCategory('shirts')

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { category: 'shirts', page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
      expect(store.productCount).toBe(1)
      expect(store.filters.categorySlug).toBe('shirts')
      expect(store.lastFetch).toEqual({ url: '/products', params: { category: 'shirts' } })
    })

    it('clears category when the same slug is clicked again', async () => {
      const store = useProductsStore()
      store.filters.categorySlug = 'shirts'
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: [], meta: { total: 0 }, categories: [], brands: [], colors: [], sizes: [] },
      })

      await store.filterProductsByCategory('shirts')

      expect(store.filters.categorySlug).toBe(null)
      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { page: 1, per_page: 4 },
      })
    })
  })

  describe('filterProductsByBrand', () => {
    it('fetches products with brand query param', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsByBrand('nike')

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { brand: 'nike', page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
      expect(store.filters.brandSlug).toBe('nike')
    })
  })

  describe('filterProductsBySize', () => {
    it('fetches products with size_id query param', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsBySize(2)

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { size_id: 2, page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
      expect(store.filters.sizeId).toBe(2)
    })
  })

  describe('filterProductsByColor', () => {
    it('fetches products with color_id query param', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsByColor(3)

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { color_id: 3, page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
      expect(store.filters.colorId).toBe(3)
    })
  })

  describe('filterProductsBySearchTerm', () => {
    it('fetches products with search query param on GET /products', async () => {
      const store = useProductsStore()
      store.searchTerm = 'shoes'
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products, meta: { total: 1 } },
      })

      await store.filterProductsBySearchTerm()

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { search: 'shoes', page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
      expect(store.lastFetch).toEqual({ url: '/products', params: { search: 'shoes' } })
    })
  })

  describe('fetchProducts with combined filters', () => {
    it('sends all active facets together', async () => {
      const store = useProductsStore()
      store.filters = {
        categorySlug: 'women',
        brandSlug: 'nike',
        colorId: 5,
        sizeId: 2,
      }
      store.searchTerm = '  run  '
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: [], meta: { total: 0 } },
      })

      await store.fetchProducts()

      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: {
          category: 'women',
          brand: 'nike',
          color_id: 5,
          size_id: 2,
          search: 'run',
          page: 1,
          per_page: 4,
        },
      })
    })
  })

  describe('clearFilters', () => {
    it('resets filter state and fetches unfiltered products', async () => {
      const store = useProductsStore()
      store.products = [{ id: 1 }]
      store.filter = 'brand'
      store.filters = {
        categorySlug: 'x',
        brandSlug: 'y',
        colorId: 1,
        sizeId: 2,
      }
      store.searchTerm = 'q'
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
      expect(store.filters).toEqual({
        categorySlug: null,
        brandSlug: null,
        colorId: null,
        sizeId: null,
      })
      expect(store.searchTerm).toBe('')
      expect(store.productsPerPage).toBe(4)
      expect(store.currentPage).toBe(1)
      expect(axios.get).toHaveBeenCalledWith('/products', {
        params: { page: 1, per_page: 4 },
      })
      expect(store.products).toEqual(products)
    })
  })
})
