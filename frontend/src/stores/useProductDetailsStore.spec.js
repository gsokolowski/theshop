import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useProductDetailsStore } from './useProductDetailsStore'

vi.mock('vue-toastification', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  }),
}))

describe('useProductDetailsStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
    vi.spyOn(console, 'error').mockImplementation(() => {})
  })

  describe('initial state', () => {
    it('has correct initial state', () => {
      const store = useProductDetailsStore()
      expect(store.product).toEqual([])
      expect(store.productThumbnail).toBe('')
      expect(store.productImages).toEqual([])
      expect(store.isLoading).toBe(false)
      expect(store.errorMessage).toBe('')
      expect(store.reviewToUpdate).toEqual({
        updating: false,
        data: {
          title: '',
          body: '',
          rating: 0,
          id: null,
        },
      })
    })
  })

  describe('getters', () => {
    it('getProduct returns product', () => {
      const store = useProductDetailsStore()
      const product = { id: 1, name: 'Product' }
      store.product = product
      expect(store.getProduct).toEqual(product)
    })

    it('getProductThumbnail returns productThumbnail', () => {
      const store = useProductDetailsStore()
      store.productThumbnail = '/img.jpg'
      expect(store.getProductThumbnail).toBe('/img.jpg')
    })

    it('getProductImages returns productImages', () => {
      const store = useProductDetailsStore()
      const images = [{ id: 1, src: '/1.jpg' }]
      store.productImages = images
      expect(store.getProductImages).toEqual(images)
    })

    it('getErrorMessage returns errorMessage', () => {
      const store = useProductDetailsStore()
      store.errorMessage = 'Error'
      expect(store.getErrorMessage).toBe('Error')
    })

    it('getIsLoading returns isLoading', () => {
      const store = useProductDetailsStore()
      store.isLoading = true
      expect(store.getIsLoading).toBe(true)
    })

    it('getReviews returns reviews from product', () => {
      const store = useProductDetailsStore()
      const reviews = [{ id: 1, rating: 5 }]
      store.product = { reviews }
      expect(store.getReviews).toEqual(reviews)
    })

    it('getReviews returns empty array when no product', () => {
      const store = useProductDetailsStore()
      store.product = {}
      expect(store.getReviews).toEqual([])
    })

    it('getReviewToUpdate returns reviewToUpdate', () => {
      const store = useProductDetailsStore()
      store.reviewToUpdate = { updating: true, data: { id: 1 } }
      expect(store.getReviewToUpdate).toEqual({ updating: true, data: { id: 1 } })
    })

    it('getAverageRating returns 0 when no reviews', () => {
      const store = useProductDetailsStore()
      store.product = { reviews: [] }
      expect(store.getAverageRating).toBe(0)
    })

    it('getAverageRating returns average rounded to nearest 0.5', () => {
      const store = useProductDetailsStore()
      store.product = {
        reviews: [
          { id: 1, rating: 5 },
          { id: 2, rating: 4 },
        ],
      }
      expect(store.getAverageRating).toBe(4.5)
    })
  })

  describe('fetchProduct', () => {
    it('fetches product and sets state on success', async () => {
      const store = useProductDetailsStore()
      const productData = {
        id: 1,
        name: 'Product',
        thumbnail: '/thumb.jpg',
        first_image: '/img1.jpg',
        second_image: '/img2.jpg',
      }
      vi.mocked(axios.get).mockResolvedValue({
        data: { data: productData },
      })

      await store.fetchProduct('product-slug')

      expect(axios.get).toHaveBeenCalledWith('/api/products/product-slug')
      expect(store.product).toEqual(productData)
      expect(store.productThumbnail).toBe('/thumb.jpg')
      expect(store.productImages).toHaveLength(2)
      expect(store.productImages[0]).toEqual({ id: 1, src: '/img1.jpg' })
      expect(store.productImages[1]).toEqual({ id: 2, src: '/img2.jpg' })
      expect(store.isLoading).toBe(false)
    })

    it('sets errorMessage on failure', async () => {
      const store = useProductDetailsStore()
      vi.mocked(axios.get).mockRejectedValue({
        response: { data: { message: 'Not found' } },
      })

      await store.fetchProduct('bad-slug')

      expect(store.errorMessage).toBe('Not found')
      expect(store.isLoading).toBe(false)
    })
  })

  describe('addReview', () => {
    it('posts review and returns response on success', async () => {
      const store = useProductDetailsStore()
      vi.mocked(axios.post).mockResolvedValue({
        data: { message: 'Review added' },
      })

      const reviewData = {
        title: 'Great',
        body: 'Nice product',
        rating: 5,
        product_id: 1,
      }
      const result = await store.addReview(reviewData)

      expect(axios.post).toHaveBeenCalledWith('/api/reviews', {
        title: 'Great',
        body: 'Nice product',
        rating: 5,
        product_id: 1,
      })
      expect(result.data.message).toBe('Review added')
    })

    it('sets errorMessage and throws on failure', async () => {
      const store = useProductDetailsStore()
      vi.mocked(axios.post).mockRejectedValue({
        response: { data: { error: 'Failed' } },
      })

      await expect(
        store.addReview({
          title: 'Bad',
          body: 'x',
          rating: 1,
          product_id: 1,
        })
      ).rejects.toBeDefined()

      expect(store.errorMessage).toBe('Failed')
    })
  })

  describe('removeReview', () => {
    it('deletes review and removes from local state on success', async () => {
      const store = useProductDetailsStore()
      const reviewToRemove = { id: 5, title: 'Remove me' }
      store.product = {
        reviews: [
          { id: 1, title: 'Keep' },
          reviewToRemove,
        ],
      }
      vi.mocked(axios.delete).mockResolvedValue({})

      await store.removeReview(reviewToRemove)

      expect(axios.delete).toHaveBeenCalledWith('/api/reviews/5')
      expect(store.product.reviews).toHaveLength(1)
      expect(store.product.reviews[0].id).toBe(1)
    })

    it('throws on failure', async () => {
      const store = useProductDetailsStore()
      vi.mocked(axios.delete).mockRejectedValue({
        response: { data: { error: 'Forbidden' } },
      })

      await expect(store.removeReview({ id: 1 })).rejects.toBeDefined()

      expect(store.errorMessage).toBe('Forbidden')
    })
  })

  describe('setReviewToUpdate', () => {
    it('sets reviewToUpdate state', () => {
      const store = useProductDetailsStore()
      const review = {
        id: 1,
        title: 'Title',
        body: 'Body',
        rating: 4,
      }
      store.setReviewToUpdate(review)

      expect(store.reviewToUpdate.updating).toBe(true)
      expect(store.reviewToUpdate.data).toEqual({
        id: 1,
        title: 'Title',
        body: 'Body',
        rating: 4,
      })
    })
  })

  describe('clearReviewToUpdate', () => {
    it('resets reviewToUpdate', () => {
      const store = useProductDetailsStore()
      store.reviewToUpdate = {
        updating: true,
        data: { id: 1, title: 'x', body: 'y', rating: 5 },
      }
      store.clearReviewToUpdate()

      expect(store.reviewToUpdate.updating).toBe(false)
      expect(store.reviewToUpdate.data).toEqual({
        title: '',
        body: '',
        rating: 0,
        id: null,
      })
    })
  })

  describe('updateReview', () => {
    it('updates review, removes from list, and clears reviewToUpdate on success', async () => {
      const store = useProductDetailsStore()
      const review = { id: 3, title: 'Updated', body: 'New', rating: 5 }
      store.product = {
        reviews: [
          { id: 1 },
          review,
        ],
      }
      vi.mocked(axios.put).mockResolvedValue({})

      const result = await store.updateReview(review)

      expect(axios.put).toHaveBeenCalledWith('/api/reviews/3', {
        title: 'Updated',
        body: 'New',
        rating: 5,
      })
      expect(store.product.reviews).toHaveLength(1)
      expect(store.product.reviews[0].id).toBe(1)
      expect(store.reviewToUpdate.updating).toBe(false)
      expect(result).toBeDefined()
    })

    it('throws on failure', async () => {
      const store = useProductDetailsStore()
      vi.mocked(axios.put).mockRejectedValue({
        response: { data: { error: 'Update failed' } },
      })

      await expect(
        store.updateReview({ id: 1, title: 'x', body: 'y', rating: 3 })
      ).rejects.toBeDefined()

      expect(store.errorMessage).toBe('Update failed')
    })
  })
})
