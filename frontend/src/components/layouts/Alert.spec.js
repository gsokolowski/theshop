import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Alert from './Alert.vue'

describe('Alert', () => {
  it('renders the alert component', () => {
    const wrapper = mount(Alert, {
      props: {
        bgColor: 'danger',
        content: 'Something went wrong.',
      },
    })
    expect(wrapper.find('.alert').exists()).toBe(true)
  })

  it('displays the content prop', () => {
    const content = 'This is an error message.'
    const wrapper = mount(Alert, {
      props: {
        bgColor: 'warning',
        content,
      },
    })
    expect(wrapper.text()).toContain(content)
  })

  it('applies bgColor as alert variant class', () => {
    const wrapper = mount(Alert, {
      props: {
        bgColor: 'danger',
        content: 'Error',
      },
    })
    expect(wrapper.find('.alert').classes()).toContain('alert-danger')
  })

  it('applies different alert variants', () => {
    const dangerWrapper = mount(Alert, {
      props: { bgColor: 'danger', content: 'Danger' },
    })
    expect(dangerWrapper.find('.alert').classes()).toContain('alert-danger')

    const warningWrapper = mount(Alert, {
      props: { bgColor: 'warning', content: 'Warning' },
    })
    expect(warningWrapper.find('.alert').classes()).toContain('alert-warning')

    const successWrapper = mount(Alert, {
      props: { bgColor: 'success', content: 'Success' },
    })
    expect(successWrapper.find('.alert').classes()).toContain('alert-success')
  })

  it('renders exclamation icon', () => {
    const wrapper = mount(Alert, {
      props: {
        bgColor: 'danger',
        content: 'Message',
      },
    })
    expect(wrapper.find('i.bi-exclamation-triangle').exists()).toBe(true)
  })
})
