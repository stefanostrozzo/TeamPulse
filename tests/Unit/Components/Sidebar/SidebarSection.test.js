import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import SidebarSection from '@/Components/Sidebar/SidebarSection.vue'

describe('SidebarSection', () => {
  it('renders without title', () => {
    const wrapper = mount(SidebarSection, {
      slots: {
        default: '<div>Section Content</div>'
      }
    })

    expect(wrapper.find('.nav-section').exists()).toBe(true)
    expect(wrapper.find('.nav-title').exists()).toBe(false)
    expect(wrapper.text()).toContain('Section Content')
  })

  it('renders with title when not collapsed', () => {
    const wrapper = mount(SidebarSection, {
      props: { title: 'Test Section' },
      slots: {
        default: '<div>Section Content</div>'
      }
    })

    expect(wrapper.find('.nav-title').exists()).toBe(true)
    expect(wrapper.find('.nav-title').text()).toBe('Test Section')
  })

  it('hides title when collapsed', () => {
    const wrapper = mount(SidebarSection, {
      props: { title: 'Test Section', collapsed: true },
      slots: {
        default: '<div>Section Content</div>'
      }
    })

    expect(wrapper.find('.nav-title').exists()).toBe(false)
  })

  it('applies correct styling to title', () => {
    const wrapper = mount(SidebarSection, {
      props: { title: 'Test Section' },
      slots: {
        default: '<div>Section Content</div>'
      }
    })

    const title = wrapper.find('.nav-title')
    expect(title.classes()).toContain('text-xs')
    expect(title.classes()).toContain('font-semibold')
    expect(title.classes()).toContain('uppercase')
    expect(title.classes()).toContain('text-gray-400')
    expect(title.classes()).toContain('tracking-wider')
    expect(title.classes()).toContain('px-3')
    expect(title.classes()).toContain('mb-3')
  })

  it('renders section content', () => {
    const wrapper = mount(SidebarSection, {
      props: { title: 'Test Section' },
      slots: {
        default: '<div class="test-content">Section Items</div>'
      }
    })

    expect(wrapper.find('.test-content').exists()).toBe(true)
    expect(wrapper.find('.test-content').text()).toBe('Section Items')
  })

  it('applies correct styling to section container', () => {
    const wrapper = mount(SidebarSection, {
      slots: {
        default: '<div>Content</div>'
      }
    })

    const section = wrapper.find('.nav-section')
    expect(section.classes()).toContain('mb-6')
  })

  it('handles empty title gracefully', () => {
    const wrapper = mount(SidebarSection, {
      props: { title: '' },
      slots: {
        default: '<div>Content</div>'
      }
    })

    expect(wrapper.find('.nav-title').exists()).toBe(false)
  })

  it('handles null title gracefully', () => {
    const wrapper = mount(SidebarSection, {
      props: { title: null },
      slots: {
        default: '<div>Content</div>'
      }
    })

    expect(wrapper.find('.nav-title').exists()).toBe(false)
  })
})
