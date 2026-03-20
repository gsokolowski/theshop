import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { useAuthStore } from '../../stores/useAuthStore'
import ProfileUpdate from './ProfileUpdate.vue'

vi.mock('axios')

function mountProfileUpdate(updateProfile = true, user = null) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const authStore = useAuthStore()
  if (user) {
    authStore.setUser(user)
    authStore.setAccessToken('test-token')
  }
  return mount(ProfileUpdate, {
    props: { updateProfile },
    global: {
      plugins: [pinia],
      stubs: { Spinner: true, ValidationErrors: true },
    },
  })
}

describe('ProfileUpdate', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.spyOn(console, 'log').mockImplementation(() => {})
  })

  it('shows Update Profile header when updateProfile is true', () => {
    const wrapper = mountProfileUpdate(true)
    expect(wrapper.text()).toContain('Update Profile')
  })

  it('shows Billing Address header when updateProfile is false', () => {
    const wrapper = mountProfileUpdate(false)
    expect(wrapper.text()).toContain('Billing Address')
  })

  it('renders form fields', () => {
    const wrapper = mountProfileUpdate(true)
    expect(wrapper.find('#name').exists()).toBe(true)
    expect(wrapper.find('#address').exists()).toBe(true)
    expect(wrapper.find('#city').exists()).toBe(true)
    expect(wrapper.find('#country').exists()).toBe(true)
    expect(wrapper.find('#zip_code').exists()).toBe(true)
    expect(wrapper.find('#phone_number').exists()).toBe(true)
  })

  it('populates form with user data on mount', async () => {
    const user = {
      name: 'John',
      address: '123 Main St',
      city: 'NYC',
      country: 'USA',
      zip_code: '10001',
      phone_number: '555-1234',
    }
    const wrapper = mountProfileUpdate(true, user)
    await wrapper.vm.$nextTick()
    expect(wrapper.find('#name').element.value).toBe('John')
  })

  it('calls axios put on form submit', async () => {
    vi.mocked(axios.put).mockResolvedValue({
      data: { user: { name: 'John' }, message: 'Updated' },
    })
    const wrapper = mountProfileUpdate(true, { name: 'John' })
    await wrapper.find('#name').setValue('Jane')
    await wrapper.find('#address').setValue('456 Oak St')
    await wrapper.find('#city').setValue('LA')
    await wrapper.find('#country').setValue('USA')
    await wrapper.find('#zip_code').setValue('90001')
    await wrapper.find('#phone_number').setValue('555-5678')
    await wrapper.find('form').trigger('submit.prevent')
    expect(axios.put).toHaveBeenCalledWith(
      '/api/user/profile/update',
      expect.objectContaining({
        name: 'Jane',
        address: '456 Oak St',
      }),
      expect.any(Object),
    )
  })
})
