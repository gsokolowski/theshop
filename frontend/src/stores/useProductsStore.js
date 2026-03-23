import { defineStore } from 'pinia'
import axios from 'axios'

// define the store and name it 'products'
export const useProductsStore = defineStore('products', {
    state: () => ({ 
        // state holds the initial values for your store's data: products, categories, brands, colors, sizes, isLoading, filter
        // state creates a new state object for each store instance - each instance has its own copy of the state
        products: [], // products array
        categories: [], // categories array
        brands: [], // brands array
        colors: [], // colors array
        sizes: [], // sizes array
        isLoading: false, // isLoading state - boolean
        filter: null, // filter state - string or null
        productsPerPage: 4, // products per page - used for initial load and Load More
        productCount: 10, // total product count from API meta (for Load More button visibility)
        currentPage: 1, // current page for pagination
        lastFetch: null, // { url, params } - tracks last fetch for Load More to call correct endpoint
        searchTerm: '', // search term state - string - default is empty string
    }),
    getters: {
        getProducts: (state) => state.products,      // Returns products array
        getCategories: (state) => state.categories,  // Returns categories array
        getBrands: (state) => state.brands,          // Returns brands array
        getColors: (state) => state.colors,          // Returns colors array
        getSizes: (state) => state.sizes,            // Returns sizes array
        // Returns products that are in stock (status = 1)
        getInStockProducts: (state) => {
            return state.products.filter(product => product.status === 1)
        },

        // product count - getter
        getProductCount: (state) => state.productCount,
    },
    actions: {     
      /***
       * How Load more button works:
       * When the user clicks the load more button, the products per page state is increased by 4.
       * When I call different action like filterProductsByBrand, fetchAllProducts, etc., I call the resetProductsPerPage action to reset the products per page state to 4.
       * I have removes local state reactive state for products per page to control
       */

      // reset pagination state when filter changes
      resetProductsPerPage() {
        this.productsPerPage = 4
        this.currentPage = 1
      },

      // Load More: fetch next page and append to products
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

      // fetch all products from the API (page 1)
      async fetchAllProducts() {
        this.isLoading = true
        this.resetProductsPerPage()
        this.lastFetch = { url: '/products', params: {} }
        try {
          const response = await axios.get('/products', {
            params: { page: 1, per_page: this.productsPerPage }
          })
          this.products = response.data.data
          this.categories = response.data.categories || []
          this.brands = response.data.brands || []
          this.colors = response.data.colors || []
          this.sizes = response.data.sizes || []
          this.productCount = response.data.meta?.total ?? response.data.data?.length ?? 0
        } catch (error) {
          console.error('Error fetching products:', error)
        } finally {
          this.isLoading = false
        }
      },
      
      // filter products by categorySlug - action
      async filterProductsByCategory(categorySlug) {
        this.resetProductsPerPage()
        this.lastFetch = { url: `/products/category/${categorySlug}`, params: {} }
        this.isLoading = true
        try {
          const response = await axios.get(`/products/category/${categorySlug}`, {
            params: { page: 1, per_page: this.productsPerPage }
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

      // filter products by brandSlug - action
      async filterProductsByBrand(brandSlug) {
        this.resetProductsPerPage()
        this.lastFetch = { url: `/products/brand/${brandSlug}`, params: {} }
        this.isLoading = true
        try {
          const response = await axios.get(`/products/brand/${brandSlug}`, {
            params: { page: 1, per_page: this.productsPerPage }
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
      
      // filter products by size - action
      async filterProductsBySize(sizeId) {
        this.resetProductsPerPage()
        this.lastFetch = { url: `/products/size/${sizeId}`, params: {} }
        this.isLoading = true
        try {
          const response = await axios.get(`/products/size/${sizeId}`, {
            params: { page: 1, per_page: this.productsPerPage }
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
      
      // filter products by color - action
      async filterProductsByColor(colorId) {
        this.resetProductsPerPage()
        this.lastFetch = { url: `/products/color/${colorId}`, params: {} }
        this.isLoading = true
        try {
          const response = await axios.get(`/products/color/${colorId}`, {
            params: { page: 1, per_page: this.productsPerPage }
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

      // filter products by searchTerm - action
      async filterProductsBySearchTerm() {
        this.resetProductsPerPage()
        this.lastFetch = { url: `/products/search/${this.searchTerm}`, params: {} }
        this.isLoading = true
        try {
          const response = await axios.get(`/products/search/${this.searchTerm}`, {
            params: { page: 1, per_page: this.productsPerPage }
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
      
      // clear filters - action
      clearFilters() {
        this.resetProductsPerPage() // Reset before fetching products
        this.filter = null
        this.products = []
        this.categories = []
        this.brands = []
        this.colors = []
        this.sizes = []
        this.fetchAllProducts()
      },
    },
  })