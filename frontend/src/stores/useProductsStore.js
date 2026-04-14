import { defineStore } from 'pinia'
import axios from 'axios'

// define the store and name it 'products'
export const useProductsStore = defineStore('products', {
    state: () => ({
        products: [],
        categories: [],
        brands: [],
        colors: [],
        sizes: [],
        isLoading: false,
        filter: null,
        // ✅ CHANGED: one active facet per dimension; combined via GET /products query params
        filters: {
            categorySlug: null,
            brandSlug: null,
            colorId: null,
            sizeId: null,
        },
        productsPerPage: 4,
        productCount: 10,
        currentPage: 1,
        lastFetch: null,
        searchTerm: '',
    }),
    getters: {
        getProducts: (state) => state.products,
        getCategories: (state) => state.categories,
        getBrands: (state) => state.brands,
        getColors: (state) => state.colors,
        getSizes: (state) => state.sizes,
        getInStockProducts: (state) => {
            return state.products.filter(product => product.status === 1)
        },
        getProductCount: (state) => state.productCount,
    },
    actions: {
      resetProductsPerPage() {
        this.productsPerPage = 4
        this.currentPage = 1
      },

      /**
       * Query params for GET /products (filters + optional search), excluding pagination.
       */
      _buildProductFilterParams() {
        const p = {}
        if (this.filters.categorySlug) {
          p.category = this.filters.categorySlug
        }
        if (this.filters.brandSlug) {
          p.brand = this.filters.brandSlug
        }
        if (this.filters.colorId != null) {
          p.color_id = this.filters.colorId
        }
        if (this.filters.sizeId != null) {
          p.size_id = this.filters.sizeId
        }
        const term = (this.searchTerm || '').trim()
        if (term) {
          p.search = term
        }
        return p
      },

      async loadMoreProducts() {
        if (!this.lastFetch) return
        this.isLoading = true
        this.currentPage += 1
        try {
          const { url, params } = this.lastFetch
          const response = await axios.get(url, {
            params: { ...params, page: this.currentPage, per_page: this.productsPerPage }
          })
          this.products = [...this.products, ...response.data.data]
        } catch (error) {
          console.error('Error loading more products:', error)
          this.currentPage -= 1
        } finally {
          this.isLoading = false
        }
      },

      /**
       * ✅ CHANGED: single fetch path for catalog — combined filters on GET /products
       */
      async fetchProducts() {
        this.isLoading = true
        const baseParams = this._buildProductFilterParams()
        this.lastFetch = { url: '/products', params: { ...baseParams } }
        try {
          const response = await axios.get('/products', {
            params: { ...baseParams, page: 1, per_page: this.productsPerPage }
          })
          this.products = response.data.data
          this.productCount = response.data.meta?.total ?? response.data.data?.length ?? 0
          if (response.data.categories) this.categories = response.data.categories
          if (response.data.brands) this.brands = response.data.brands
          if (response.data.colors) this.colors = response.data.colors
          if (response.data.sizes) this.sizes = response.data.sizes
        } catch (error) {
          console.error('Error fetching products:', error)
        } finally {
          this.isLoading = false
        }
      },

      /**
       * Full catalog (page load): clears facet state and search, then fetches page 1.
       */
      async fetchAllProducts() {
        this.filters = {
          categorySlug: null,
          brandSlug: null,
          colorId: null,
          sizeId: null,
        }
        this.searchTerm = ''
        this.resetProductsPerPage()
        await this.fetchProducts()
      },

      async filterProductsByCategory(categorySlug) {
        if (this.filters.categorySlug === categorySlug) {
          this.filters.categorySlug = null
        } else {
          this.filters.categorySlug = categorySlug
        }
        this.resetProductsPerPage()
        await this.fetchProducts()
      },

      async filterProductsByBrand(brandSlug) {
        if (this.filters.brandSlug === brandSlug) {
          this.filters.brandSlug = null
        } else {
          this.filters.brandSlug = brandSlug
        }
        this.resetProductsPerPage()
        await this.fetchProducts()
      },

      async filterProductsBySize(sizeId) {
        if (this.filters.sizeId === sizeId) {
          this.filters.sizeId = null
        } else {
          this.filters.sizeId = sizeId
        }
        this.resetProductsPerPage()
        await this.fetchProducts()
      },

      async filterProductsByColor(colorId) {
        if (this.filters.colorId === colorId) {
          this.filters.colorId = null
        } else {
          this.filters.colorId = colorId
        }
        this.resetProductsPerPage()
        await this.fetchProducts()
      },

      async filterProductsBySearchTerm() {
        this.resetProductsPerPage()
        await this.fetchProducts()
      },

      async clearFilters() {
        this.resetProductsPerPage()
        this.filter = null
        this.filters = {
          categorySlug: null,
          brandSlug: null,
          colorId: null,
          sizeId: null,
        }
        this.searchTerm = ''
        await this.fetchProducts()
      },
    },
  })
