import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Spinner from './Spinner.vue'

describe('Spinner', () => {
  it('renders the spinner component', () => {
    const mockStore = {
      isLoading: false,
    }
    const wrapper = mount(Spinner, {
      props: {
        store: mockStore,
      },
    })
    expect(wrapper.exists()).toBe(true)
  })

  it('accepts store prop with isLoading property', () => {
    const mockStore = {
      isLoading: true,
    }
    const wrapper = mount(Spinner, {
      props: {
        store: mockStore,
      },
    })
    expect(wrapper.props('store')).toEqual(mockStore)
    expect(wrapper.props('store').isLoading).toBe(true)
  })
})
