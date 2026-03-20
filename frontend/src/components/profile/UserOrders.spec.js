import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import UserOrders from './UserOrders.vue'

vi.mock('axios')

function mountUserOrders() {
  const pinia = createPinia()
  setActivePinia(pinia)
  return mount(UserOrders, {
    global: {
      plugins: [pinia],
      stubs: {
        ProfileSidebar: true,
        OrderDetailModal: true,
        Alert: { template: '<div class="alert">{{ content }}</div>', props: ['content', 'bgColor'] },
      },
    },
  })
}

describe('UserOrders', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders the component', () => {
    vi.mocked(axios.get).mockResolvedValue({ data: { data: { orders: [] } } })
    const wrapper = mountUserOrders()
    expect(wrapper.find('.row').exists()).toBe(true)
  })

  it('shows No orders yet when orders are empty', async () => {
    vi.mocked(axios.get).mockResolvedValue({ data: { data: { orders: [] } } })
    const wrapper = mountUserOrders()
    await vi.waitFor(() => {
      expect(wrapper.text()).toContain('No orders yet!')
    }, { timeout: 3000 })
  })

  it('fetches orders on mount', async () => {
    vi.mocked(axios.get).mockResolvedValue({ data: { data: { orders: [] } } })
    mountUserOrders()
    await vi.waitFor(() => {
      expect(axios.get).toHaveBeenCalledWith('/api/orders')
    }, { timeout: 2000 })
  })
})
