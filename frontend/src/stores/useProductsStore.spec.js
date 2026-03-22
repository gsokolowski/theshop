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
    it('resets productsPerPage to 4', () => {
      const store = useProductsStore()
      store.productsPerPage = 12
      store.resetProductsPerPage()
      expect(store.productsPerPage).toBe(4)
    })
  })

  describe('loadMoreProducts', () => {
    it('increases productsPerPage by 4', () => {
      const store = useProductsStore()
      store.productsPerPage = 4
      store.loadMoreProducts()
      expect(store.productsPerPage).toBe(8)
      store.loadMoreProducts()
      expect(store.productsPerPage).toBe(12)
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
          categories,
          brands,
          colors,
          sizes,
        },
      })

      await store.fetchAllProducts()

      expect(axios.get).toHaveBeenCalledWith('/products')
      expect(store.products).toEqual(products)
      expect(store.categories).toEqual(categories)
      expect(store.brands).toEqual(brands)
      expect(store.colors).toEqual(colors)
      expect(store.sizes).toEqual(sizes)
      expect(store.productCount).toBe(1)
      expect(store.productsPerPage).toBe(4)
      expect(store.isLoading).toBe(false)
    })
  })

  describe('filterProductsByCategory', () => {
    it('fetches products by category slug', async () => {
      const store = useProductsStore()
      const products = [{ id: 1, name: 'Shirt' }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products },
      })

      await store.filterProductsByCategory('shirts')

      expect(axios.get).toHaveBeenCalledWith('/products/category/shirts')
      expect(store.products).toEqual(products)
      expect(store.productCount).toBe(1)
      expect(store.productsPerPage).toBe(4)
    })
  })

  describe('filterProductsByBrand', () => {
    it('fetches products by brand slug', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products },
      })

      await store.filterProductsByBrand('nike')

      expect(axios.get).toHaveBeenCalledWith('/products/brand/nike')
      expect(store.products).toEqual(products)
    })
  })

  describe('filterProductsBySize', () => {
    it('fetches products by size id', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products },
      })

      await store.filterProductsBySize(2)

      expect(axios.get).toHaveBeenCalledWith('/products/size/2')
      expect(store.products).toEqual(products)
    })
  })

  describe('filterProductsByColor', () => {
    it('fetches products by color id', async () => {
      const store = useProductsStore()
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products },
      })

      await store.filterProductsByColor(3)

      expect(axios.get).toHaveBeenCalledWith('/products/color/3')
      expect(store.products).toEqual(products)
    })
  })

  describe('filterProductsBySearchTerm', () => {
    it('fetches products by search term', async () => {
      const store = useProductsStore()
      store.searchTerm = 'shoes'
      const products = [{ id: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: products },
      })

      await store.filterProductsBySearchTerm()

      expect(axios.get).toHaveBeenCalledWith('/products/search/shoes')
      expect(store.products).toEqual(products)
    })
  })

  describe('clearFilters', () => {
    it('resets state and fetches all products', async () => {
      const store = useProductsStore()
      store.products = [{ id: 1 }]
      store.filter = 'brand'
      store.productsPerPage = 12
      const products = [{ id: 1, status: 1 }]
      vi.mocked(axios.get).mockResolvedValue({
        data: {
          data: products,
          categories: [],
          brands: [],
          colors: [],
          sizes: [],
        },
      })

      await store.clearFilters()

      expect(store.filter).toBe(null)
      expect(store.productsPerPage).toBe(4)
      expect(axios.get).toHaveBeenCalledWith('/products')
      expect(store.products).toEqual(products)
    })
  })
})
