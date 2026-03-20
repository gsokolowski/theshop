import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductsStore } from '../../stores/useProductsStore'
import Categories from './Categories.vue'

function mountCategories(storeData = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const store = useProductsStore()
  store.categories = storeData.categories || []
  return mount(Categories, {
    global: { plugins: [pinia] },
  })
}

describe('Categories', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the Categories heading', () => {
    const wrapper = mountCategories()
    expect(wrapper.find('h6').text()).toBe('Categories')
  })

  it('renders nothing when categories list is empty', () => {
    const wrapper = mountCategories({ categories: [] })
    expect(wrapper.findAll('li.nav-item')).toHaveLength(0)
  })

  it('renders category buttons when categories are present', () => {
    const categories = [
      { id: 1, name: 'Shoes', slug: 'shoes' },
      { id: 2, name: 'Shirts', slug: 'shirts' },
    ]
    const wrapper = mountCategories({ categories })
    const buttons = wrapper.findAll('button.btn-link')
    expect(buttons).toHaveLength(2)
    expect(buttons[0].text()).toBe('Shoes')
    expect(buttons[1].text()).toBe('Shirts')
  })

  it('calls filterProductsByCategory with slug when category is clicked', async () => {
    const categories = [{ id: 1, name: 'Shoes', slug: 'shoes' }]
    const wrapper = mountCategories({ categories })
    const productsStore = useProductsStore()
    vi.spyOn(productsStore, 'filterProductsByCategory').mockResolvedValue(undefined)

    await wrapper.find('button.btn-link').trigger('click')

    expect(productsStore.filterProductsByCategory).toHaveBeenCalledWith('shoes')
  })
})
