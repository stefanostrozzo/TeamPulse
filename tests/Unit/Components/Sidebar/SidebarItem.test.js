import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import SidebarItem from '@/Components/Sidebar/SidebarItem.vue'

describe('SidebarItem', () => {
  it('renders with default props', () => {
    const wrapper = mount(SidebarItem, {
      slots: {
        default: 'Test Item',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    expect(wrapper.text()).toContain('Test Item')
    expect(wrapper.find('.nav-icon').exists()).toBe(true)
    expect(wrapper.find('.nav-item').classes()).toContain('text-gray-300')
  })

  it('applies active state correctly', () => {
    const wrapper = mount(SidebarItem, {
      props: { active: true },
      slots: {
        default: 'Active Item',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    expect(wrapper.find('.nav-item').classes()).toContain('bg-violet-500/20')
    expect(wrapper.find('.nav-item').classes()).toContain('text-violet-400')
    expect(wrapper.find('.nav-item').classes()).toContain('font-medium')
  })

  it('shows badge when provided and not collapsed', () => {
    const wrapper = mount(SidebarItem, {
      props: { badge: '5' },
      slots: {
        default: 'Item with Badge',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    expect(wrapper.find('.badge').text()).toBe('5')
    expect(wrapper.find('.badge').classes()).toContain('bg-violet-600')
  })

  it('hides badge when collapsed', () => {
    const wrapper = mount(SidebarItem, {
      props: { badge: '5', collapsed: true },
      slots: {
        default: 'Item with Badge',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    expect(wrapper.find('.badge').exists()).toBe(false)
  })

  it('hides text when collapsed', () => {
    const wrapper = mount(SidebarItem, {
      props: { collapsed: true },
      slots: {
        default: 'Hidden Text',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    expect(wrapper.find('.nav-text').exists()).toBe(false)
  })

  it('shows right slot when provided', () => {
    const wrapper = mount(SidebarItem, {
      slots: {
        default: 'Test Item',
        icon: '<i class="fas fa-home"></i>',
        right: '<i class="fas fa-chevron-right"></i>'
      }
    })

    // Right slot renders at the end of the button
    const rightIcon = wrapper.find('.nav-item > .ml-auto')
    expect(rightIcon.exists()).toBe(true)
  })

  it('emits click event when clicked', async () => {
    const wrapper = mount(SidebarItem, {
      slots: {
        default: 'Clickable Item',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    await wrapper.find('.nav-item').trigger('click')
    expect(wrapper.emitted('click')).toBeTruthy()
    expect(wrapper.emitted('click')).toHaveLength(1)
  })

  it('applies hover styles on mouseover', async () => {
    const wrapper = mount(SidebarItem, {
      slots: {
        default: 'Hover Item',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    const navItem = wrapper.find('.nav-item')
    expect(navItem.classes()).toContain('hover:bg-violet-500/15')
    expect(navItem.classes()).toContain('hover:text-violet-300')
  })

  it('maintains icon size when collapsed', () => {
    const wrapper = mount(SidebarItem, {
      props: { collapsed: true },
      slots: {
        default: 'Test Item',
        icon: '<i class="fas fa-home"></i>'
      }
    })

    const navIcon = wrapper.find('.nav-icon')
    expect(navIcon.classes()).toContain('w-6')
    expect(navIcon.classes()).toContain('h-6')
    expect(navIcon.classes()).toContain('flex-shrink-0')
  })
})
