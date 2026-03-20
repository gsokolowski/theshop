import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import axios from 'axios'
import VerifyEmail from './VerifyEmail.vue'

vi.mock('axios')

const routes = [
  { path: '/', component: { template: '<div></div>' } },
  { path: '/login', component: { template: '<div>Login</div>' } },
  { path: '/:pathMatch(.*)*', component: { template: '<div></div>' } },
]

function mountVerifyEmail() {
  const router = createRouter({ history: createMemoryHistory(), routes })
  return mount(VerifyEmail, {
    global: {
      plugins: [router],
    },
  })
}

describe('VerifyEmail', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    Object.defineProperty(window, 'location', {
      value: { search: '?id=1&expires=123&signature=abc' },
      writable: true,
    })
  })

  it('renders Email Verification heading', () => {
    vi.mocked(axios.get).mockResolvedValue({ data: { status: 200, message: 'Verified' } })
    const wrapper = mountVerifyEmail()
    expect(wrapper.find('h2').text()).toBe('Email Verification')
  })

  it('shows loading state initially', () => {
    vi.mocked(axios.get).mockImplementation(() => new Promise(() => {}))
    const wrapper = mountVerifyEmail()
    expect(wrapper.text()).toContain('Verifying your email...')
  })

  it('shows success message when verification succeeds', async () => {
    vi.mocked(axios.get).mockResolvedValue({ data: { status: 200, message: 'Email verified' } })
    const wrapper = mountVerifyEmail()
    await vi.waitFor(() => {
      expect(wrapper.text()).toContain('Email Verified Successfully!')
    }, { timeout: 2000 })
  })

  it('shows error message when verification fails', async () => {
    vi.mocked(axios.get).mockRejectedValue(new Error('Verification failed'))
    const wrapper = mountVerifyEmail()
    await vi.waitFor(() => {
      expect(wrapper.text()).toContain('Verification Failed')
    }, { timeout: 2000 })
  })

  it('shows already verified message when email is already verified', async () => {
    vi.mocked(axios.get).mockResolvedValue({
      data: { status: 200, message: 'Email is already verified' },
    })
    const wrapper = mountVerifyEmail()
    await vi.waitFor(() => {
      expect(wrapper.text()).toContain('Already Verified')
    }, { timeout: 2000 })
  })
})
