import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useWishlistStore } from '../../stores/useWishlistStore'
import UserWishlist from './UserWishlist.vue'

const routes = [
  { path: '/', component: { template: '<div></div>' } },
  { path: '/product/:slug', name: 'product', component: { template: '<div>Product</div>' } },
  { path: '/:pathMatch(.*)*', component: { template: '<div></div>' } },
]

function mountUserWishlist() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const wishlistStore = useWishlistStore()
  vi.spyOn(wishlistStore, 'fetchWishlist').mockResolvedValue(undefined)
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(UserWishlist, {
    global: {
      plugins: [pinia, router],
      stubs: {
        ProfileSidebar: true,
        Alert: { template: '<div class="alert">{{ content }}</div>', props: ['content', 'bgColor'] },
        AddToCartModal: true,
        Spinner: true,
      },
    },
  })
}

describe('UserWishlist', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the component', () => {
    const wrapper = mountUserWishlist()
    expect(wrapper.find('.row').exists()).toBe(true)
  })

  it('shows No wishlist items when wishlist is empty', async () => {
    const wrapper = mountUserWishlist()
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('No wishlist items yet!')
  })

  it('calls fetchWishlist on mount', () => {
    mountUserWishlist()
    const wishlistStore = useWishlistStore()
    expect(wishlistStore.fetchWishlist).toHaveBeenCalled()
  })
})
