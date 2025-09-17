import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import SidebarLogo from '@/Components/Sidebar/SidebarLogo.vue'

describe('SidebarLogo', () => {
  it('renders with default content', () => {
    const wrapper = mount(SidebarLogo)

    expect(wrapper.find('.logo-container').exists()).toBe(true)
    expect(wrapper.find('.logo-icon').exists()).toBe(true)
    expect(wrapper.find('.logo-text').exists()).toBe(true)
    expect(wrapper.text()).toContain('TeamPulse')
  })

  it('shows custom icon in slot', () => {
    const wrapper = mount(SidebarLogo, {
      slots: {
        icon: '<i class="fas fa-custom-icon"></i>'
      }
    })

    expect(wrapper.find('.logo-icon').html()).toContain('fa-custom-icon')
  })

  it('shows custom text in slot', () => {
    const wrapper = mount(SidebarLogo, {
      slots: {
        default: 'Custom Brand'
      }
    })

    expect(wrapper.text()).toContain('Custom Brand')
  })

  it('hides text when collapsed', () => {
    const wrapper = mount(SidebarLogo, {
      props: { collapsed: true }
    })

    expect(wrapper.find('.logo-text').exists()).toBe(false)
  })

  it('shows text when not collapsed', () => {
    const wrapper = mount(SidebarLogo, {
      props: { collapsed: false }
    })

    expect(wrapper.find('.logo-text').exists()).toBe(true)
  })

  it('applies correct padding when not collapsed', () => {
    const wrapper = mount(SidebarLogo, {
      props: { collapsed: false }
    })

    expect(wrapper.find('.logo-container').classes()).toContain('pr-10')
  })

  it('does not apply extra padding when collapsed', () => {
    const wrapper = mount(SidebarLogo, {
      props: { collapsed: true }
    })

    expect(wrapper.find('.logo-container').classes()).not.toContain('pr-10')
  })

  it('renders toggle button', () => {
    const wrapper = mount(SidebarLogo)

    const toggleButton = wrapper.find('.sidebar-toggle')
    expect(toggleButton.exists()).toBe(true)
    expect(toggleButton.attributes('type')).toBe('button')
  })

  it('shows chevron right when collapsed', () => {
    const wrapper = mount(SidebarLogo, {
      props: { collapsed: true }
    })

    const chevronIcon = wrapper.find('.fa-chevron-right')
    expect(chevronIcon.exists()).toBe(true)
  })

  it('shows chevron left when not collapsed', () => {
    const wrapper = mount(SidebarLogo, {
      props: { collapsed: false }
    })

    const chevronIcon = wrapper.find('.fa-chevron-left')
    expect(chevronIcon.exists()).toBe(true)
  })

  it('emits toggle event when button clicked', async () => {
    const wrapper = mount(SidebarLogo)

    await wrapper.find('.sidebar-toggle').trigger('click')
    expect(wrapper.emitted('toggle')).toBeTruthy()
    expect(wrapper.emitted('toggle')).toHaveLength(1)
  })

  it('applies correct styling to toggle button', () => {
    const wrapper = mount(SidebarLogo)

    const toggleButton = wrapper.find('.sidebar-toggle')
    expect(toggleButton.classes()).toContain('absolute')
    expect(toggleButton.classes()).toContain('top-1/2')
    expect(toggleButton.classes()).toContain('-translate-y-1/2')
    expect(toggleButton.classes()).toContain('-right-4')
    expect(toggleButton.classes()).toContain('w-8')
    expect(toggleButton.classes()).toContain('h-8')
    expect(toggleButton.classes()).toContain('rounded-full')
    expect(toggleButton.classes()).toContain('bg-gray-700')
  })

  it('maintains icon size and positioning', () => {
    const wrapper = mount(SidebarLogo)

    const logoIcon = wrapper.find('.logo-icon')
    expect(logoIcon.classes()).toContain('w-9')
    expect(logoIcon.classes()).toContain('h-9')
    expect(logoIcon.classes()).toContain('flex-shrink-0')
  })
})
