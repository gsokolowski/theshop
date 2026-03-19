import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ValidationErrors from './ValidationErrors.vue'

describe('ValidationErrors', () => {
  it('renders nothing when errors object is empty', () => {
    const wrapper = mount(ValidationErrors, {
      props: {
        errors: {},
      },
    })
    expect(wrapper.find('.alert').exists()).toBe(false)
  })

  it('renders nothing when errors is undefined (uses default)', () => {
    const wrapper = mount(ValidationErrors)
    expect(wrapper.find('.alert').exists()).toBe(false)
  })

  it('displays validation errors when present', () => {
    const errors = {
      email: ['The email field is required.'],
      password: ['The password must be at least 8 characters.'],
    }
    const wrapper = mount(ValidationErrors, {
      props: {
        errors,
      },
    })
    expect(wrapper.find('.alert.alert-danger').exists()).toBe(true)
    expect(wrapper.text()).toContain('The email field is required.')
    expect(wrapper.text()).toContain('The password must be at least 8 characters.')
  })

  it('displays multiple errors for the same field', () => {
    const errors = {
      email: ['Invalid format.', 'Email already exists.'],
    }
    const wrapper = mount(ValidationErrors, {
      props: {
        errors,
      },
    })
    expect(wrapper.text()).toContain('Invalid format.')
    expect(wrapper.text()).toContain('Email already exists.')
  })
})
