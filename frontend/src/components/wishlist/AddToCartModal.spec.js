import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import AddToCartModal from './AddToCartModal.vue'

const mockProduct = {
  id: 1,
  name: 'Test Product',
  price: 49.99,
  thumbnail: '/img.jpg',
  status: 1,
  qty: 10,
  colors: [
    { id: 1, name: '#ff0000' },
    { id: 2, name: '#00ff00' },
  ],
  sizes: [
    { id: 1, name: 'S' },
    { id: 2, name: 'M' },
    { id: 3, name: 'L' },
  ],
}

function mountAddToCartModal(props = {}, attachToBody = true) {
  return mount(AddToCartModal, {
    props: {
      product: mockProduct,
      show: true,
      ...props,
    },
    attachTo: attachToBody ? document.body : undefined,
  })
}

function getModalRoot() {
  return document.body.querySelector('.modal')
}

describe('AddToCartModal', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    document.body.innerHTML = ''
  })

  it('renders the modal when show is true', () => {
    mountAddToCartModal()
    const modal = getModalRoot()
    expect(modal).toBeTruthy()
    expect(modal?.textContent).toContain('Add to Cart')
  })

  it('does not render modal when show is false', () => {
    mountAddToCartModal({ show: false })
    const modal = getModalRoot()
    expect(modal).toBeFalsy()
  })

  it('displays product information', () => {
    mountAddToCartModal()
    const modal = getModalRoot()
    expect(modal?.textContent).toContain('Test Product')
    expect(modal?.textContent).toContain('49.99')
  })

  it('displays color selection options', () => {
    mountAddToCartModal()
    const modal = getModalRoot()
    expect(modal?.textContent).toContain('Select Color')
  })

  it('displays size selection options', () => {
    mountAddToCartModal()
    const modal = getModalRoot()
    expect(modal?.textContent).toContain('Select Size')
    expect(modal?.textContent).toContain('S')
    expect(modal?.textContent).toContain('M')
  })

  it('displays quantity input', () => {
    mountAddToCartModal()
    const modal = getModalRoot()
    expect(modal?.textContent).toContain('Quantity')
    expect(modal?.querySelector('input[type="number"]')).toBeTruthy()
  })

  it('emits close when Cancel button is clicked', async () => {
    const wrapper = mountAddToCartModal()
    const cancelBtn = getModalRoot()?.querySelector('button.btn-secondary')
    await cancelBtn?.click()
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  it('emits close when close button is clicked', async () => {
    const wrapper = mountAddToCartModal()
    const closeBtn = getModalRoot()?.querySelector('button.btn-close')
    await closeBtn?.click()
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  it('emits close when backdrop is clicked', async () => {
    const wrapper = mountAddToCartModal()
    const backdrop = getModalRoot()?.querySelector('.modal-backdrop')
    await backdrop?.click()
    expect(wrapper.emitted('close')).toBeTruthy()
  })

  it('emits add-to-cart with correct data when form is valid', async () => {
    const wrapper = mountAddToCartModal()
    const modal = getModalRoot()
    const colorDiv = modal?.querySelector('[title="#ff0000"]')
    colorDiv?.click()
    await wrapper.vm.$nextTick()
    const sizeBtns = modal?.querySelectorAll('button.btn-sm')
    const sizeM = Array.from(sizeBtns || []).find(b => b.textContent?.trim() === 'M')
    sizeM?.click()
    await wrapper.vm.$nextTick()
    const qtyInput = modal?.querySelector('input[type="number"]')
    if (qtyInput) {
      qtyInput.value = 2
      qtyInput.dispatchEvent(new Event('input'))
    }
    await wrapper.vm.$nextTick()
    const addBtn = Array.from(modal?.querySelectorAll('button.btn-primary') || []).find(b => b.textContent?.includes('Add to Cart'))
    addBtn?.click()
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('add-to-cart')).toBeTruthy()
    if (wrapper.emitted('add-to-cart')) {
      const payload = wrapper.emitted('add-to-cart')[0][0]
      expect(payload.product).toEqual(mockProduct)
      expect(payload.qty).toBe(2)
    }
  })

  it('Add to Cart button is disabled when color and size not selected', () => {
    mountAddToCartModal()
    const modal = getModalRoot()
    const addBtn = Array.from(modal?.querySelectorAll('button.btn-primary') || []).find(b => b.textContent?.includes('Add to Cart'))
    expect(addBtn?.hasAttribute('disabled')).toBe(true)
  })

  it('does not emit add-to-cart when clicked without selections', async () => {
    const wrapper = mountAddToCartModal()
    const modal = getModalRoot()
    const addBtn = Array.from(modal?.querySelectorAll('button.btn-primary') || []).find(b => b.textContent?.includes('Add to Cart'))
    addBtn?.click()
    await wrapper.vm.$nextTick()
    expect(wrapper.emitted('add-to-cart')).toBeFalsy()
  })
})
