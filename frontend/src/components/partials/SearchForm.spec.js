import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductsStore } from '../../stores/useProductsStore'
import SearchForm from './SearchForm.vue'

function mountSearchForm(storeData = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const store = useProductsStore()
  store.searchTerm = storeData.searchTerm ?? ''
  return mount(SearchForm, {
    global: { plugins: [pinia] },
  })
}

describe('SearchForm', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the Search Products heading', () => {
    const wrapper = mountSearchForm()
    expect(wrapper.find('h6').text()).toBe('Search Products')
  })

  it('renders search input with placeholder', () => {
    const wrapper = mountSearchForm()
    const input = wrapper.find('input[type="text"]')
    expect(input.exists()).toBe(true)
    expect(input.attributes('placeholder')).toBe('search')
  })

  it('renders search button with icon', () => {
    const wrapper = mountSearchForm()
    const button = wrapper.find('button[type="submit"]')
    expect(button.exists()).toBe(true)
    expect(wrapper.find('i.bi-search').exists()).toBe(true)
  })

  it('binds input to productsStore.searchTerm', async () => {
    const wrapper = mountSearchForm()
    const input = wrapper.find('input')
    const productsStore = useProductsStore()

    await input.setValue('sneakers')

    expect(productsStore.searchTerm).toBe('sneakers')
  })

  it('calls filterProductsBySearchTerm on form submit', async () => {
    const wrapper = mountSearchForm()
    const productsStore = useProductsStore()
    vi.spyOn(productsStore, 'filterProductsBySearchTerm').mockResolvedValue(undefined)

    await wrapper.find('form').trigger('submit.prevent')

    expect(productsStore.filterProductsBySearchTerm).toHaveBeenCalled()
  })
})
