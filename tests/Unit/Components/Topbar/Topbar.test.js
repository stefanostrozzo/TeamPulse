import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import Topbar from '@/Components/Topbar/Topbar.vue'

describe('Topbar', () => {
  it('renders with default elements', () => {
    const wrapper = mount(Topbar)

    expect(wrapper.find('.topbar').exists()).toBe(true)
    expect(wrapper.find('.search-input').exists()).toBe(true)
    expect(wrapper.find('.search-icon').exists()).toBe(true)
    expect(wrapper.find('.search-container').exists()).toBe(true)
  })

  it('displays the default placeholder text', () => {
    const wrapper = mount(Topbar)

    expect(wrapper.find('.search-input').attributes('placeholder')).toBe('Cerca qualcosa...')
  })

  it('renders search icon with SVG', () => {
    const wrapper = mount(Topbar)

    const searchIcon = wrapper.find('.search-icon')
    expect(searchIcon.exists()).toBe(true)
    expect(searchIcon.find('svg').exists()).toBe(true)
  })

  it('applies correct styling to topbar container', () => {
    const wrapper = mount(Topbar)

    const topbar = wrapper.find('.topbar')
    expect(topbar.classes()).toContain('bg-gray-800')
    expect(topbar.classes()).toContain('border-b')
    expect(topbar.classes()).toContain('border-gray-700')
    expect(topbar.classes()).toContain('px-6')
    expect(topbar.classes()).toContain('flex')
    expect(topbar.classes()).toContain('items-center')
    expect(topbar.classes()).toContain('justify-between')
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
    expect(searchInput.classes()).toContain('text-white')
  })

  it('applies correct styling to search icon', () => {
    const wrapper = mount(Topbar)

    const searchIcon = wrapper.find('.search-icon')
    expect(searchIcon.classes()).toContain('absolute')
    expect(searchIcon.classes()).toContain('left-4')
    expect(searchIcon.classes()).toContain('top-1/2')
    expect(searchIcon.classes()).toContain('-translate-y-1/2')
    expect(searchIcon.classes()).toContain('text-gray-400')
  })

  it('has a relative container for search dropdown positioning', () => {
    const wrapper = mount(Topbar)

    const container = wrapper.find('.search-container')
    expect(container.classes()).toContain('relative')
    expect(container.classes()).toContain('w-full')
    expect(container.classes()).toContain('max-w-xl')
  })

  it('does not show search dropdown by default', () => {
    const wrapper = mount(Topbar)

    // The dropdown is conditionally rendered with v-if="isDropdownOpen && results.length > 0"
    const dropdown = wrapper.find('.absolute.top-full')
    expect(dropdown.exists()).toBe(false)
  })

  it('binds search input to v-model', async () => {
    const wrapper = mount(Topbar)

    const input = wrapper.find('.search-input')
    await input.setValue('test query')
    expect(input.element.value).toBe('test query')
  })

  it('has the correct input type', () => {
    const wrapper = mount(Topbar)

    const input = wrapper.find('.search-input')
    expect(input.attributes('type')).toBe('text')
  })

  it('applies transition styling to search input', () => {
    const wrapper = mount(Topbar)

    const searchInput = wrapper.find('.search-input')
    expect(searchInput.classes()).toContain('transition-all')
  })

  it('applies border-transparent to search input by default', () => {
    const wrapper = mount(Topbar)

    const searchInput = wrapper.find('.search-input')
    expect(searchInput.classes()).toContain('border-transparent')
  })

  it('applies focus styling classes to search input', () => {
    const wrapper = mount(Topbar)

    const searchInput = wrapper.find('.search-input')
    expect(searchInput.classes()).toContain('focus:border-[#07b4f6]')
    expect(searchInput.classes()).toContain('focus:ring-0')
  })
})
