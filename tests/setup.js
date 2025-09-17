import { config } from '@vue/test-utils'
import { createPinia } from 'pinia'

// Mock Inertia.js
global.Inertia = {
  visit: vi.fn(),
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  patch: vi.fn(),
  delete: vi.fn(),
  reload: vi.fn(),
  replace: vi.fn(),
  remember: vi.fn(),
  restore: vi.fn(),
}

// Mock route helper
global.route = vi.fn((name, params = {}) => {
  const routes = {
    'home': '/',
    'profile.edit': '/profile',
    'logout': '/logout',
  }
  return routes[name] || '/'
})

// Mock usePage
global.usePage = vi.fn(() => ({
  props: {
    auth: {
      user: {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        hasManagementPermissions: true,
      }
    }
  }
}))

// Global test utilities
config.global.plugins = [createPinia()]

// Mock Font Awesome
global.HTMLElement.prototype.insertAdjacentHTML = vi.fn()
