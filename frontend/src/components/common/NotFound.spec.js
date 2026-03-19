import { describe, it, expect } from 'vitest'
import { mountWithPlugins } from '../../test-utils'
import NotFound from './NotFound.vue'

describe('NotFound', () => {
  it('renders 404 message', () => {
    const wrapper = mountWithPlugins(NotFound)
    expect(wrapper.text()).toContain('404')
    expect(wrapper.text()).toContain('Page Not Found')
    expect(wrapper.text()).toContain('The page you are looking for does not exist or has been moved.')
  })

  it('renders Go Home link', () => {
    const wrapper = mountWithPlugins(NotFound)
    const link = wrapper.find('a.btn-primary')
    expect(link.exists()).toBe(true)
    expect(link.attributes('href')).toBe('/')
    expect(link.text()).toContain('Go Home')
  })
})
