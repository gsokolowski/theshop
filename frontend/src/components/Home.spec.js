import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, shallowMount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useProductsStore } from '../stores/useProductsStore'
import Home from './Home.vue'

vi.mock('axios')

function mountHome() {
  const pinia = createPinia()
  setActivePinia(pinia)
  return mount(Home, {
    global: {
      plugins: [pinia],
      stubs: {
        Sidebar: { template: '<div data-test="sidebar"></div>' },
        ProductsList: { template: '<div data-test="products-list"></div>' },
      },
    },
  })
}

const mockProductsResponse = {
  data: { data: [], categories: [], brands: [], colors: [], sizes: [] },
}

describe('Home', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(axios.get).mockResolvedValue(mockProductsResponse)
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders the Home component', () => {
    const wrapper = mountHome()
    expect(wrapper.find('.row').exists()).toBe(true)
  })

  it('renders Sidebar component', () => {
    const wrapper = mountHome()
    expect(wrapper.find('[data-test="sidebar"]').exists()).toBe(true)
  })

  it('renders ProductsList component', () => {
    const wrapper = mountHome()
    expect(wrapper.find('[data-test="products-list"]').exists()).toBe(true)
  })

  it('calls clearFilters on mount', async () => {
    const pinia = createPinia()
    setActivePinia(pinia)
    const productsStore = useProductsStore()
    const clearSpy = vi.spyOn(productsStore, 'clearFilters')
    shallowMount(Home, {
      global: {
        plugins: [pinia],
        stubs: {
          Sidebar: { template: '<div data-test="sidebar"></div>' },
          ProductsList: { template: '<div data-test="products-list"></div>' },
        },
      },
    })
    await new Promise(r => setTimeout(r, 50))
    expect(clearSpy).toHaveBeenCalled()
  })

  it('has min-vh-100 class for full height', () => {
    const wrapper = mountHome()
    expect(wrapper.find('.row').classes()).toContain('min-vh-100')
  })
})
