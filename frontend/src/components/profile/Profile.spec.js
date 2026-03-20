import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import Profile from './Profile.vue'

function mountProfile() {
  const pinia = createPinia()
  setActivePinia(pinia)
  return mount(Profile, {
    global: {
      plugins: [pinia],
      stubs: { ProfileSidebar: true, ProfileUpdate: true },
    },
  })
}

describe('Profile', () => {
  it('renders the profile layout', () => {
    const wrapper = mountProfile()
    expect(wrapper.find('.row').exists()).toBe(true)
  })

  it('renders ProfileSidebar', () => {
    const wrapper = mountProfile()
    expect(wrapper.findComponent({ name: 'ProfileSidebar' }).exists()).toBe(true)
  })

  it('renders ProfileUpdate with updateProfile prop', () => {
    const wrapper = mountProfile()
    const profileUpdate = wrapper.findComponent({ name: 'ProfileUpdate' })
    expect(profileUpdate.exists()).toBe(true)
    expect(profileUpdate.props('updateProfile')).toBe(true)
  })
})
