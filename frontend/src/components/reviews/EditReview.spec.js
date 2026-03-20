import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
import EditReview from './EditReview.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/login', component: { template: '<div>Login</div>' } },
]

function mountEditReview() {
  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(EditReview, {
    global: {
      plugins: [pinia, router],
      stubs: {
        Spinner: { template: '<div data-test="spinner"></div>' },
        StarRating: { template: '<div data-test="star-rating"></div>' },
      },
    },
  })
}

describe('EditReview', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders the EditReview card', () => {
    const wrapper = mountEditReview()
    expect(wrapper.find('.card').exists()).toBe(true)
    expect(wrapper.text()).toContain('Edit your review')
  })

  it('has title and body inputs', () => {
    const wrapper = mountEditReview()
    expect(wrapper.find('#edit-title').exists()).toBe(true)
    expect(wrapper.find('#edit-body').exists()).toBe(true)
  })

  it('has Update Review and Cancel buttons', () => {
    const wrapper = mountEditReview()
    expect(wrapper.text()).toContain('Update Review')
    expect(wrapper.text()).toContain('Cancel')
  })

  it('calls clearReviewToUpdate when Cancel is clicked', async () => {
    const wrapper = mountEditReview()
    const productStore = useProductDetailsStore()
    productStore.reviewToUpdate = { updating: true, data: { id: 1, title: 'Old', body: 'Old body', rating: 4 } }
    const clearSpy = vi.spyOn(productStore, 'clearReviewToUpdate')
    await wrapper.vm.$nextTick()
    const cancelBtn = wrapper.find('button.btn-secondary')
    await cancelBtn.trigger('click')
    expect(clearSpy).toHaveBeenCalled()
  })

  it('populates form when reviewToUpdate is set', async () => {
    const wrapper = mountEditReview()
    const productStore = useProductDetailsStore()
    productStore.reviewToUpdate = {
      updating: true,
      data: { id: 1, title: 'My Review', body: 'Great product!', rating: 5 },
    }
    await wrapper.vm.$nextTick()
    const titleInput = wrapper.find('#edit-title')
    const bodyInput = wrapper.find('#edit-body')
    expect(titleInput.element.value).toBe('My Review')
    expect(bodyInput.element.value).toBe('Great product!')
  })

  it('calls updateReview on form submit when valid', async () => {
    const wrapper = mountEditReview()
    const productStore = useProductDetailsStore()
    productStore.reviewToUpdate = {
      updating: true,
      data: { id: 1, title: 'Old', body: 'Old body', rating: 4 },
    }
    const updateSpy = vi.spyOn(productStore, 'updateReview').mockResolvedValue({ data: { message: 'Updated' } })
    await wrapper.vm.$nextTick()
    await wrapper.find('#edit-title').setValue('Updated title')
    await wrapper.find('#edit-body').setValue('Updated body')
    await wrapper.find('form').trigger('submit.prevent')
    expect(updateSpy).toHaveBeenCalledWith(
      expect.objectContaining({
        id: 1,
        title: 'Updated title',
        body: 'Updated body',
      })
    )
  })
})
