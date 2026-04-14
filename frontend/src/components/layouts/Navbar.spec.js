import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useAuthStore } from '../../stores/useAuthStore'
import { useCartStore } from '../../stores/useCartStore'
import { useProductsStore } from '../../stores/useProductsStore'
import Navbar from './Navbar.vue'

const defaultRoutes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/login', component: { template: '<div>Login</div>' } },
  { path: '/register', component: { template: '<div>Register</div>' } },
  { path: '/profile', component: { template: '<div>Profile</div>' } },
  { path: '/user/orders', component: { template: '<div>Orders</div>' } },
  { path: '/user/wishlist', component: { template: '<div>Wishlist</div>' } },
  { path: '/about', component: { template: '<div>About</div>' } },
  { path: '/cart', component: { template: '<div>Cart</div>' } },
  { path: '/:pathMatch(.*)*', component: { template: '<div>NotFound</div>' } },
]

function mountNavbar(options = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)

  if (options.auth) {
    const authStore = useAuthStore()
    authStore.setUserLoggedIn(options.auth.loggedIn)
    if (options.auth.user) authStore.setUser(options.auth.user)
  }
  if (options.cart) {
    const cartStore = useCartStore()
    cartStore.cartItems = options.cart.items || []
  }

  const router = createRouter({
    history: createMemoryHistory(),
    routes: options.routes || defaultRoutes,
  })
  return mount(Navbar, {
    global: {
      plugins: [pinia, router],
      ...options.global,
    },
    ...options,
  })
}

describe('Navbar', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('renders the navbar with brand link', () => {
    const wrapper = mountNavbar()
    expect(wrapper.find('header').exists()).toBe(true)
    expect(wrapper.find('.navbar-brand').text()).toBe('The Shop')
    expect(wrapper.find('.navbar-brand').attributes('href')).toBe('/')
  })

  it('shows Register and Login links when user is not logged in', () => {
    const wrapper = mountNavbar({ auth: { loggedIn: false } })
    expect(wrapper.text()).toContain('Register')
    expect(wrapper.text()).toContain('Login')
  })

  it('shows user name, Orders, Wishlist and Logout when user is logged in', () => {
    const wrapper = mountNavbar({ auth: { loggedIn: true, user: { name: 'John Doe' } } })
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Orders')
    expect(wrapper.text()).toContain('Wishlist')
    expect(wrapper.text()).toContain('Logout')
    expect(wrapper.text()).not.toContain('Register')
    expect(wrapper.text()).not.toContain('Login')
  })

  it('displays cart items count', () => {
    const wrapper = mountNavbar({ cart: { items: [{ id: 1 }, { id: 2 }, { id: 3 }] } })
    expect(wrapper.text()).toContain('Cart(3)')
  })

  it('displays Cart(0) when cart is empty', () => {
    const wrapper = mountNavbar()
    expect(wrapper.text()).toContain('Cart(0)')
  })

  it('has Home, About and Cart links', () => {
    const wrapper = mountNavbar()
    const links = wrapper.findAll('.nav-link')
    const linkTexts = links.map(link => link.text())
    expect(linkTexts.some(t => t.includes('Home'))).toBe(true)
    expect(linkTexts.some(t => t.includes('About'))).toBe(true)
    expect(linkTexts.some(t => t.includes('Cart'))).toBe(true)
  })

  it('calls productsStore.clearFilters when brand link is clicked', async () => {
    const wrapper = mountNavbar()
    const productsStore = useProductsStore()
    vi.spyOn(productsStore, 'fetchAllProducts').mockResolvedValue(undefined)
    const clearFiltersSpy = vi.spyOn(productsStore, 'clearFilters')
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {})

    await wrapper.find('.navbar-brand').trigger('click')

    expect(clearFiltersSpy).toHaveBeenCalled()
    consoleSpy.mockRestore()
  })

  it('calls logout and clearCart when Logout is clicked', async () => {
    const wrapper = mountNavbar({ auth: { loggedIn: true, user: { name: 'John' } } })
    const authStore = useAuthStore()
    const logoutSpy = vi.spyOn(authStore, 'logout').mockResolvedValue(undefined)
    const cartStore = useCartStore()
    const clearCartSpy = vi.spyOn(cartStore, 'clearCart')

    const logoutLink = wrapper.find('a[href="#"]')
    await logoutLink.trigger('click.prevent')

    expect(logoutSpy).toHaveBeenCalled()
    expect(clearCartSpy).toHaveBeenCalledWith(false)

    logoutSpy.mockRestore()
    clearCartSpy.mockRestore()
  })
})
