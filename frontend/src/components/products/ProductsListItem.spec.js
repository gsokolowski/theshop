import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useWishlistStore } from '../../stores/useWishlistStore'
import ProductsListItem from './ProductsListItem.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/product/:slug', name: 'product', component: { template: '<div>Product</div>' } },
]

const mockProduct = {
  id: 1,
  name: 'Test Product',
  slug: 'test-product',
  description: 'A test product description that is longer than fifty characters for testing',
  price: 29.99,
  thumbnail: null,
  brand: { name: 'Test Brand' },
  reviews: [],
}

function mountProductsListItem(product = mockProduct) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const wishlistStore = useWishlistStore()
  vi.spyOn(wishlistStore, 'fetchWishlist').mockResolvedValue(undefined)
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(ProductsListItem, {
    props: { product },
    global: {
      plugins: [pinia, router],
      stubs: { StarRating: true },
    },
  })
}

describe('ProductsListItem', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders product name', () => {
    const wrapper = mountProductsListItem()
    expect(wrapper.find('.card-title').text()).toBe('Test Product')
  })

  it('renders product price', () => {
    const wrapper = mountProductsListItem()
    expect(wrapper.text()).toContain('$29.99')
  })

  it('renders brand name', () => {
    const wrapper = mountProductsListItem()
    expect(wrapper.text()).toContain('Brand: Test Brand')
  })

  it('links image and title to product detail page', () => {
    const wrapper = mountProductsListItem()
    const links = wrapper.findAllComponents({ name: 'RouterLink' })
    expect(links.length).toBe(2)
    const expectedTo = { name: 'product', params: { slug: 'test-product' } }
    expect(links[0].props('to')).toEqual(expectedTo)
    expect(links[1].props('to')).toEqual(expectedTo)
  })

  it('calls toggleWishlist when wishlist button clicked', async () => {
    const wrapper = mountProductsListItem()
    const wishlistStore = useWishlistStore()
    vi.spyOn(wishlistStore, 'toggleWishlist').mockResolvedValue(undefined)

    await wrapper.find('button.btn-outline-secondary').trigger('click.stop')

    expect(wishlistStore.toggleWishlist).toHaveBeenCalledWith(1)
  })
})
