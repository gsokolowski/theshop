import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductsStore } from '../../stores/useProductsStore'
import Brands from './Brands.vue'

function mountBrands(storeData = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const store = useProductsStore()
  store.brands = storeData.brands || []
  return mount(Brands, {
    global: { plugins: [pinia] },
  })
}

describe('Brands', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the Brands heading', () => {
    const wrapper = mountBrands()
    expect(wrapper.find('h6').text()).toBe('Brands')
  })

  it('renders nothing when brands list is empty', () => {
    const wrapper = mountBrands({ brands: [] })
    expect(wrapper.findAll('li.nav-item')).toHaveLength(0)
  })

  it('renders brand buttons when brands are present', () => {
    const brands = [
      { id: 1, name: 'Nike', slug: 'nike' },
      { id: 2, name: 'Adidas', slug: 'adidas' },
    ]
    const wrapper = mountBrands({ brands })
    const buttons = wrapper.findAll('button.btn-link')
    expect(buttons).toHaveLength(2)
    expect(buttons[0].text()).toBe('Nike')
    expect(buttons[1].text()).toBe('Adidas')
  })

  it('calls filterProductsByBrand with slug when brand is clicked', async () => {
    const brands = [{ id: 1, name: 'Nike', slug: 'nike' }]
    const wrapper = mountBrands({ brands })
    const productsStore = useProductsStore()
    vi.spyOn(productsStore, 'filterProductsByBrand').mockResolvedValue(undefined)

    await wrapper.find('button.btn-link').trigger('click')

    expect(productsStore.filterProductsByBrand).toHaveBeenCalledWith('nike')
  })
})
