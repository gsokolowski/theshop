import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductsStore } from '../../stores/useProductsStore'
import Sidebar from './Sidebar.vue'

const defaultStubs = {
  SearchForm: true,
  Categories: true,
  Brands: true,
  Sizes: true,
  Colors: true,
}

function mountSidebar(stubs = defaultStubs) {
  const pinia = createPinia()
  setActivePinia(pinia)
  return mount(Sidebar, {
    global: {
      plugins: [pinia],
      stubs,
    },
  })
}

describe('Sidebar', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders the sidebar', () => {
    const wrapper = mountSidebar()
    expect(wrapper.find('aside').exists()).toBe(true)
    expect(wrapper.find('aside').classes()).toContain('col-md-4')
    expect(wrapper.find('aside').classes()).toContain('bg-light')
  })

  it('displays Filters heading', () => {
    const wrapper = mountSidebar()
    expect(wrapper.find('h4').text()).toBe('Filters')
  })

  it('displays Clear all button', () => {
    const wrapper = mountSidebar()
    const clearBtn = wrapper.find('button.btn-link')
    expect(clearBtn.exists()).toBe(true)
    expect(clearBtn.text()).toContain('Clear all')
  })

  it('calls productsStore.clearFilters when Clear all is clicked', async () => {
    const wrapper = mountSidebar()
    const productsStore = useProductsStore()
    vi.spyOn(productsStore, 'fetchProducts').mockResolvedValue(undefined)
    const clearFiltersSpy = vi.spyOn(productsStore, 'clearFilters')

    await wrapper.find('button.btn-link').trigger('click')

    expect(clearFiltersSpy).toHaveBeenCalled()
    clearFiltersSpy.mockRestore()
  })

  it('styles Clear all as primary when any filter or search is active', async () => {
    const wrapper = mountSidebar()
    const store = useProductsStore()
    const btn = () => wrapper.find('button.btn-link')

    expect(btn().classes()).toContain('text-dark')
    expect(btn().classes()).not.toContain('text-primary')

    store.filters.categorySlug = 'test-slug'
    await wrapper.vm.$nextTick()
    expect(btn().classes()).toContain('text-primary')
    expect(btn().classes()).toContain('fw-semibold')

    store.filters = { categorySlug: null, brandSlug: null, colorId: null, sizeId: null }
    await wrapper.vm.$nextTick()
    expect(btn().classes()).toContain('text-dark')

    store.searchTerm = 'query'
    await wrapper.vm.$nextTick()
    expect(btn().classes()).toContain('text-primary')
  })

  it('renders SearchForm, Categories, Brands, Sizes, and Colors components', () => {
    const wrapper = mountSidebar({
      SearchForm: { template: '<div data-test="search-form"></div>' },
      Categories: { template: '<div data-test="categories"></div>' },
      Brands: { template: '<div data-test="brands"></div>' },
      Sizes: { template: '<div data-test="sizes"></div>' },
      Colors: { template: '<div data-test="colors"></div>' },
    })
    expect(wrapper.find('[data-test="search-form"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="categories"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="brands"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="sizes"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="colors"]').exists()).toBe(true)
  })
})
