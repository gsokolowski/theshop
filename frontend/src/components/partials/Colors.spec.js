import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductsStore } from '../../stores/useProductsStore'
import Colors from './Colors.vue'

function mountColors(storeData = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const store = useProductsStore()
  store.colors = storeData.colors || []
  return mount(Colors, {
    global: { plugins: [pinia] },
  })
}

describe('Colors', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the Colors heading', () => {
    const wrapper = mountColors()
    expect(wrapper.find('h6').text()).toBe('Colors')
  })

  it('renders nothing when colors list is empty', () => {
    const wrapper = mountColors({ colors: [] })
    expect(wrapper.findAll('.border')).toHaveLength(0)
  })

  it('renders color swatches when colors are present', () => {
    const colors = [
      { id: 1, name: 'red' },
      { id: 2, name: '#00ff00' },
    ]
    const wrapper = mountColors({ colors })
    const swatches = wrapper.findAll('.border')
    expect(swatches).toHaveLength(2)
  })

  it('applies background color from color name', () => {
    const colors = [{ id: 1, name: 'blue' }]
    const wrapper = mountColors({ colors })
    const swatch = wrapper.find('.border')
    expect(swatch.attributes('style')).toContain('background-color')
    expect(swatch.attributes('style')).toContain('blue')
  })

  it('calls filterProductsByColor with id when color is clicked', async () => {
    const colors = [{ id: 5, name: 'red' }]
    const wrapper = mountColors({ colors })
    const productsStore = useProductsStore()
    vi.spyOn(productsStore, 'filterProductsByColor').mockResolvedValue(undefined)

    await wrapper.find('.border').trigger('click')

    expect(productsStore.filterProductsByColor).toHaveBeenCalledWith(5)
  })
})
