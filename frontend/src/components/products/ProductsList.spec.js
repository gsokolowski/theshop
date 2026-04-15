import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductsStore } from '../../stores/useProductsStore'
import ProductsList from './ProductsList.vue'

function mountProductsList() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const store = useProductsStore()
  vi.spyOn(store, 'fetchProducts').mockResolvedValue(undefined)
  return mount(ProductsList, {
    global: {
      plugins: [pinia],
      stubs: { Spinner: true, ProductsListItem: { template: '<div class="product-item"></div>' } },
    },
  })
}

describe('ProductsList', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders the component', () => {
    const wrapper = mountProductsList()
    expect(wrapper.find('.col-md-8').exists()).toBe(true)
  })

  it('shows Found X products when products exist', async () => {
    const wrapper = mountProductsList()
    const store = useProductsStore()
    store.products = [{ id: 1, name: 'Product 1' }]
    store.productCount = 1
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Found 1 products')
  })

  it('shows No products found when products are empty', () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    const store = useProductsStore()
    store.products = []
    store.productCount = 0
    vi.spyOn(store, 'fetchProducts').mockResolvedValue(undefined)
    const wrapper = mount(ProductsList, {
      global: {
        plugins: [pinia],
        stubs: { Spinner: true, ProductsListItem: { template: '<div class="product-item"></div>' } },
      },
    })
    expect(wrapper.text()).toContain('No products found')
  })

  it('calls fetchProducts on mount', () => {
    mountProductsList()
    const store = useProductsStore()
    expect(store.fetchProducts).toHaveBeenCalled()
  })

  it('shows Load More button when more products available', async () => {
    const wrapper = mountProductsList()
    const store = useProductsStore()
    store.products = Array(4).fill({ id: 1 }) // 4 loaded
    store.productCount = 10 // 10 total - more to load
    await wrapper.vm.$nextTick()
    expect(wrapper.find('button[name="loadMore"]').exists()).toBe(true)
    expect(wrapper.text()).toContain('Load More')
  })

  it('calls loadMoreProducts when Load More clicked', async () => {
    const wrapper = mountProductsList()
    const store = useProductsStore()
    store.products = Array(4).fill({ id: 1 })
    store.productCount = 10
    await wrapper.vm.$nextTick()
    const loadMoreSpy = vi.spyOn(store, 'loadMoreProducts')
    await wrapper.find('button[name="loadMore"]').trigger('click')
    expect(loadMoreSpy).toHaveBeenCalled()
  })
})
