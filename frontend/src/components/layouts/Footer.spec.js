import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Footer from './Footer.vue'

describe('Footer', () => {
  it('renders the footer', () => {
    const wrapper = mount(Footer)
    expect(wrapper.find('footer').exists()).toBe(true)
    expect(wrapper.find('footer').classes()).toContain('bg-light')
  })

  it('displays copyright text', () => {
    const wrapper = mount(Footer)
    expect(wrapper.text()).toContain('© 2024 The Shop')
    expect(wrapper.text()).toContain('All rights reserved.')
  })

  it('has centered text', () => {
    const wrapper = mount(Footer)
    expect(wrapper.find('.text-center').exists()).toBe(true)
    expect(wrapper.find('.text-center').text()).toContain('© 2024 The Shop')
  })
})
