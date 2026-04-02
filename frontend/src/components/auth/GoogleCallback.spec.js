import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'
import GoogleCallback from './GoogleCallback.vue'

const routes = [
  { path: '/', component: { template: '<div>Home</div>' } },
  { path: '/login', component: { template: '<div>Login</div>' } },
]

function mountGoogleCallback(routeQuery = {}) {
  const pinia = createPinia()
  setActivePinia(pinia)
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  })
  return mount(GoogleCallback, {
    global: {
      plugins: [pinia, router],
      stubs: { Spinner: true },
    },
    props: {},
    mocks: {
      $route: { query: routeQuery },
    },
  })
}

describe('GoogleCallback', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders Authenticating message', () => {
    const wrapper = mountGoogleCallback()
    expect(wrapper.text()).toContain('Authenticating...')
    expect(wrapper.text()).toContain('Please wait while we log you in.')
  })

  it('renders Spinner component', () => {
    const wrapper = mountGoogleCallback()
    expect(wrapper.findComponent({ name: 'Spinner' }).exists()).toBe(true)
  })
})
