import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import Topbar from '@/Components/Topbar/Topbar.vue'

describe('Topbar', () => {
  it('renders with default props', () => {
    const wrapper = mount(Topbar)

    expect(wrapper.find('.topbar').exists()).toBe(true)
    expect(wrapper.find('.search-input').exists()).toBe(true)
    expect(wrapper.find('.command-palette-btn').exists()).toBe(true)
    expect(wrapper.find('.action-btn').exists()).toBe(true)
  })

  it('displays correct placeholder text', () => {
    const wrapper = mount(Topbar, {
      props: { placeholder: 'Custom search...' }
    })

    expect(wrapper.find('.search-input').attributes('placeholder')).toBe('Custom search...')
  })

  it('shows notification count', () => {
    const wrapper = mount(Topbar, {
      props: { notifications: 5 }
    })

    const badge = wrapper.find('.absolute.-top-1.-right-1')
    expect(badge.text()).toBe('5')
    expect(badge.classes()).toContain('bg-red-500')
  })

  it('hides notification badge when count is 0', () => {
    const wrapper = mount(Topbar, {
      props: { notifications: 0 }
    })

    const badge = wrapper.find('.absolute.-top-1.-right-1')
    expect(badge.text()).toBe('0')
  })

  it('emits toggle-notifications event when notification button clicked', async () => {
    const wrapper = mount(Topbar)

    await wrapper.find('.action-btn').trigger('click')
    expect(wrapper.emitted('toggle-notifications')).toBeTruthy()
    expect(wrapper.emitted('toggle-notifications')).toHaveLength(1)
  })

  it('emits command event when command palette button clicked', async () => {
    const wrapper = mount(Topbar)

    await wrapper.find('.command-palette-btn').trigger('click')
    expect(wrapper.emitted('command')).toBeTruthy()
    expect(wrapper.emitted('command')).toHaveLength(1)
  })

  it('applies correct styling to search input', () => {
    const wrapper = mount(Topbar)

    const searchInput = wrapper.find('.search-input')
    expect(searchInput.classes()).toContain('w-full')
    expect(searchInput.classes()).toContain('bg-gray-700')
    expect(searchInput.classes()).toContain('rounded-xl')
    expect(searchInput.classes()).toContain('pl-11')
    expect(searchInput.classes()).toContain('pr-4')
    expect(searchInput.classes()).toContain('py-2')
    expect(searchInput.classes()).toContain('text-sm')
    expect(searchInput.classes()).toContain('text-gray-100')
  })

  it('applies correct styling to command palette button', () => {
    const wrapper = mount(Topbar)

    const commandBtn = wrapper.find('.command-palette-btn')
    expect(commandBtn.classes()).toContain('flex')
    expect(commandBtn.classes()).toContain('items-center')
    expect(commandBtn.classes()).toContain('gap-2')
    expect(commandBtn.classes()).toContain('bg-gray-700')
    expect(commandBtn.classes()).toContain('text-gray-100')
    expect(commandBtn.classes()).toContain('rounded-xl')
    expect(commandBtn.classes()).toContain('px-3')
    expect(commandBtn.classes()).toContain('py-2')
    expect(commandBtn.classes()).toContain('text-sm')
  })

  it('applies correct styling to notification button', () => {
    const wrapper = mount(Topbar)

    const notificationBtn = wrapper.find('.action-btn')
    expect(notificationBtn.classes()).toContain('w-10')
    expect(notificationBtn.classes()).toContain('h-10')
    expect(notificationBtn.classes()).toContain('rounded-xl')
    expect(notificationBtn.classes()).toContain('bg-gray-700')
    expect(notificationBtn.classes()).toContain('text-gray-100')
    expect(notificationBtn.classes()).toContain('hover:bg-violet-600')
  })

  it('renders search icon', () => {
    const wrapper = mount(Topbar)

    const searchIcon = wrapper.find('.search-icon')
    expect(searchIcon.exists()).toBe(true)
    expect(searchIcon.find('svg').exists()).toBe(true)
  })

  it('renders command palette icon', () => {
    const wrapper = mount(Topbar)

    const commandIcon = wrapper.find('.command-palette-btn svg')
    expect(commandIcon.exists()).toBe(true)
  })

  it('renders notification bell icon', () => {
    const wrapper = mount(Topbar)

    const bellIcon = wrapper.find('.action-btn svg')
    expect(bellIcon.exists()).toBe(true)
  })

  it('applies hover effects correctly', () => {
    const wrapper = mount(Topbar)

    const commandBtn = wrapper.find('.command-palette-btn')
    const notificationBtn = wrapper.find('.action-btn')

    expect(commandBtn.classes()).toContain('hover:bg-violet-600')
    expect(notificationBtn.classes()).toContain('hover:bg-violet-600')
  })
})
