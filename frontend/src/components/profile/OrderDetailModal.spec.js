import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import OrderDetailModal from './OrderDetailModal.vue'

const mockOrder = {
  id: 1,
  total: 99.99,
  created_at: '2024-01-15',
  deliverd_at: '2024-01-20',
  products: [
    {
      id: 1,
      name: 'Test Product',
      price: 49.99,
      description: 'A test product',
      thumbnail: null,
      pivot: { size_id: 1, color_id: 1 },
      sizes: [{ id: 1, name: 'M' }],
      colors: [{ id: 1, name: 'red' }],
    },
  ],
}

describe('OrderDetailModal', () => {
  it('renders nothing when show is false', () => {
    const wrapper = mount(OrderDetailModal, {
      props: { show: false, order: null },
      global: {
        stubs: { Teleport: { template: '<div><slot /></div>' } },
      },
    })
    expect(wrapper.find('.modal').exists()).toBe(false)
  })

  it('renders modal content when show is true', () => {
    const wrapper = mount(OrderDetailModal, {
      props: { show: true, order: mockOrder },
      global: {
        stubs: { Teleport: { template: '<div><slot /></div>' } },
      },
    })
    expect(wrapper.text()).toContain('Order #1')
  })

  it('displays order total', () => {
    const wrapper = mount(OrderDetailModal, {
      props: { show: true, order: mockOrder },
      global: {
        stubs: { Teleport: { template: '<div><slot /></div>' } },
      },
    })
    expect(wrapper.text()).toContain('99.99')
  })

  it('emits close when close button clicked', async () => {
    const wrapper = mount(OrderDetailModal, {
      props: { show: true, order: mockOrder },
      global: {
        stubs: { Teleport: { template: '<div><slot /></div>' } },
      },
    })
    await wrapper.find('.btn-close').trigger('click')
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  it('emits close when backdrop clicked', async () => {
    const wrapper = mount(OrderDetailModal, {
      props: { show: true, order: mockOrder },
      global: {
        stubs: { Teleport: { template: '<div><slot /></div>' } },
      },
    })
    await wrapper.find('.modal-backdrop').trigger('click')
    expect(wrapper.emitted('close')).toHaveLength(1)
  })
})
