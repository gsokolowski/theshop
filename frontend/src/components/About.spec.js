import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import About from './About.vue'

function mountAbout() {
  return mount(About, {})
}

describe('About', () => {
  it('renders the About page', () => {
    const wrapper = mountAbout()
    expect(wrapper.find('.row').exists()).toBe(true)
    expect(wrapper.text()).toContain('About')
  })

  it('displays Sidebar with links', () => {
    const wrapper = mountAbout()
    const sidebar = wrapper.find('aside')
    expect(sidebar.exists()).toBe(true)
    expect(sidebar.text()).toContain('Sidebar')
    expect(wrapper.find('ul.list-unstyled').exists()).toBe(true)
    const links = wrapper.findAll('a')
    expect(links.length).toBeGreaterThanOrEqual(3)
  })

  it('displays main content area', () => {
    const wrapper = mountAbout()
    const mainContent = wrapper.find('.col-md-9')
    expect(mainContent.exists()).toBe(true)
    expect(mainContent.text()).toContain('About')
    expect(mainContent.text()).toContain('Bootstrap')
  })

  it('has correct column layout classes', () => {
    const wrapper = mountAbout()
    expect(wrapper.find('aside').classes()).toContain('col-md-3')
    expect(wrapper.find('.col-md-9').exists()).toBe(true)
  })
})
