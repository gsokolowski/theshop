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
        productsPerPage: 4, // products per page state - number
        productCount:10, // product count state - number - default is 10
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
       * I have removes local state reactive state for products per page to control everythong in the store.
       */

      // reset products per page state to 4 when the filter is changed action
      resetProductsPerPage() {
        this.productsPerPage = 4 // reset products per page state to 4
        console.log('Products per page reset to:', this.productsPerPage)
      },

      // On button click, increase products per page state by 4 - action
      loadMoreProducts() {
          this.productsPerPage += 4 // increase products per page state by 4
          console.log('Products per page increased to:', this.productsPerPage)
      },

      // fetch all products from the API
      async fetchAllProducts() {
        // set the isLoading state to true
        this.isLoading = true
        this.resetProductsPerPage() // Reset before fetching products
        // try to fetch the products from the API
        try {
          // fetch the products from the API
          const response = await axios.get('/api/products') // use axios.defaults.baseURL to get the products
          //console.log('Response:', response)
          this.products = response.data.data //Access nested data property
          this.categories = response.data.categories // access the categories property
          this.brands = response.data.brands // access the brands property
          this.colors = response.data.colors // access the colors property
          this.sizes = response.data.sizes // access the sizes property
          this.productCount = response.data.data.length // access the product count property
        } catch (error) {
          console.error('Error fetching products:', error)
        } finally {
          this.isLoading = false
        }
      },
      
      // filter products by brand - action
      async filterProductsByBrand(brand) {
        console.log('Filtering products by brand:', brand)
        this.resetProductsPerPage() // Reset before fetching products
        // set the isLoading state to true
        this.isLoading = true
        // try to fetch the products from the API
        try {
          // fetch the products from the API
          const response = await axios.get(`/api/products/brand/${brand}`) // use axios.defaults.baseURL to get the products
          this.products = response.data.data //Access nested data property
          this.productCount = response.data.data.length
          console.log('Product count:', this.productCount)
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