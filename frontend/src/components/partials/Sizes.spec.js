import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductsStore } from '../../stores/useProductsStore'
import Sizes from './Sizes.vue'

function mountSizes(storeData = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const store = useProductsStore()
  store.sizes = storeData.sizes || []
  return mount(Sizes, {
    global: { plugins: [pinia] },
  })
}

describe('Sizes', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the Sizes heading', () => {
    const wrapper = mountSizes()
    expect(wrapper.find('h6').text()).toBe('Sizes')
  })

  it('renders nothing when sizes list is empty', () => {
    const wrapper = mountSizes({ sizes: [] })
    expect(wrapper.findAll('.d-flex > div')).toHaveLength(0)
  })

  it('renders size buttons when sizes are present', () => {
    const sizes = [
      { id: 1, name: 'S' },
      { id: 2, name: 'M' },
      { id: 3, name: 'L' },
    ]
    const wrapper = mountSizes({ sizes })
    const sizeDivs = wrapper.findAll('.d-flex > div')
    expect(sizeDivs).toHaveLength(3)
    expect(sizeDivs[0].text()).toBe('S')
    expect(sizeDivs[1].text()).toBe('M')
    expect(sizeDivs[2].text()).toBe('L')
  })

  it('calls filterProductsBySize with id when size is clicked', async () => {
    const sizes = [{ id: 3, name: 'L' }]
    const wrapper = mountSizes({ sizes })
    const productsStore = useProductsStore()
    vi.spyOn(productsStore, 'filterProductsBySize').mockResolvedValue(undefined)

    await wrapper.find('.d-flex > div').trigger('click')

    expect(productsStore.filterProductsBySize).toHaveBeenCalledWith(3)
  })
})
