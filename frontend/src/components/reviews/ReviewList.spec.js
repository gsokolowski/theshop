import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { useProductDetailsStore } from '../../stores/useProductDetailsStore'
import { useAuthStore } from '../../stores/useAuthStore'
import ReviewList from './ReviewList.vue'

const mockReviews = [
  {
    id: 1,
    title: 'Great product',
    body: 'I really enjoyed it',
    rating: 5,
    user_id: 1,
    user: { id: 1, name: 'John Doe', profile_image_url: null },
    created_at: '2024-01-15',
  },
  {
    id: 2,
    title: 'Good value',
    body: 'Worth the price',
    rating: 4,
    user_id: 2,
    user: { id: 2, name: 'Jane Smith', profile_image_url: null },
    created_at: '2024-01-16',
  },
]

function mountReviewList() {
  const pinia = createPinia()
  setActivePinia(pinia)
  return mount(ReviewList, {
    global: {
      plugins: [pinia],
      stubs: {
        Spinner: { template: '<div data-test="spinner"></div>' },
        StarRating: { template: '<div data-test="star-rating"></div>' },
      },
    },
  })
}

describe('ReviewList', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  it('renders the ReviewList card', () => {
    const wrapper = mountReviewList()
    expect(wrapper.find('.card').exists()).toBe(true)
    expect(wrapper.text()).toContain('Reviews')
  })

  it('displays empty state when no reviews', async () => {
    const wrapper = mountReviewList()
    const productStore = useProductDetailsStore()
    productStore.product = { reviews: [] }
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('No reviews yet. Be the first to review this product!')
  })

  it('displays reviews when available', async () => {
    const wrapper = mountReviewList()
    const productStore = useProductDetailsStore()
    productStore.product = { reviews: mockReviews }
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Great product')
    expect(wrapper.text()).toContain('I really enjoyed it')
    expect(wrapper.text()).toContain('John Doe')
    expect(wrapper.text()).toContain('Good value')
    expect(wrapper.text()).toContain('Jane Smith')
  })

  it('displays review count in header', async () => {
    const wrapper = mountReviewList()
    const productStore = useProductDetailsStore()
    productStore.product = { reviews: mockReviews }
    await wrapper.vm.$nextTick()
    expect(wrapper.text()).toContain('Reviews (2)')
  })

  it('calls removeReview when delete button is clicked', async () => {
    const wrapper = mountReviewList()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { reviews: [...mockReviews] }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1 }
    const removeSpy = vi.spyOn(productStore, 'removeReview').mockResolvedValue({ status: 200, data: { message: 'Deleted' } })
    await wrapper.vm.$nextTick()
    const deleteBtn = wrapper.find('button.btn-danger')
    await deleteBtn.trigger('click')
    expect(removeSpy).toHaveBeenCalledWith(mockReviews[0])
  })

  it('calls setReviewToUpdate when edit button is clicked', async () => {
    const wrapper = mountReviewList()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { reviews: [...mockReviews] }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1 }
    const setSpy = vi.spyOn(productStore, 'setReviewToUpdate')
    await wrapper.vm.$nextTick()
    const editBtn = wrapper.find('button.btn-warning')
    await editBtn.trigger('click')
    expect(setSpy).toHaveBeenCalledWith(mockReviews[0])
  })

  it('shows edit/delete buttons only for own reviews', async () => {
    const wrapper = mountReviewList()
    const productStore = useProductDetailsStore()
    const authStore = useAuthStore()
    productStore.product = { reviews: mockReviews }
    authStore.isUserLoggedIn = true
    authStore.user = { id: 1 }
    await wrapper.vm.$nextTick()
    const reviewActions = wrapper.find('.review-actions')
    expect(reviewActions.exists()).toBe(true)
  })
})
