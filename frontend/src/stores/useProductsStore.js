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
        // Returns products per page state - number - getter
        getProductsPerPage: (state) => state.productsPerPage, 

        // all products count - getter
        getAllProductsCount: (state) => state.products.length,
    },
    actions: {
      // fetch all products from the API
      async fetchAllProducts() {
        // set the isLoading state to true
        this.isLoading = true
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
        } catch (error) {
          console.error('Error fetching products:', error)
        } finally {
          this.isLoading = false
        }
      },      
    },
  })