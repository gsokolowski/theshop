import { mount } from '@vue/test-utils'
import { createPinia } from 'pinia'
import { createRouter, createMemoryHistory } from 'vue-router'

/**
 * Mount a component with Pinia store support.
 * Use when testing components that use Pinia stores.
 */
export function mountWithPinia(component, options = {}) {
  const pinia = createPinia()
  return mount(component, {
    global: {
      plugins: [pinia],
      ...options.global,
    },
    ...options,
  })
}

/**
 * Mount a component with Pinia and Vue Router.
 * Use when testing components that use both stores and routing (e.g. router-link).
 */
export function mountWithPlugins(component, options = {}) {
  const pinia = createPinia()
  const router = createRouter({
    history: createMemoryHistory(),
    routes: options.routes || [
      { path: '/', name: 'home', component: { template: '<div>Home</div>' } },
      { path: '/:pathMatch(.*)*', name: 'not-found', component: { template: '<div>NotFound</div>' } },
    ],
  })

  return mount(component, {
    global: {
      plugins: [pinia, router],
      ...options.global,
    },
    ...options,
  })
}

// Re-export Vue Test Utils for convenience
export { mount } from '@vue/test-utils'
