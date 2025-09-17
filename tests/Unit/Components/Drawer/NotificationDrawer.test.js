import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import NotificationDrawer from '@/Components/Drawer/NotificationDrawer.vue'

describe('NotificationDrawer', () => {
  it('renders when closed', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: false }
    })

    expect(wrapper.find('.notification-drawer').exists()).toBe(true)
    expect(wrapper.find('.overlay').exists()).toBe(false)
  })

  it('renders when open', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    expect(wrapper.find('.notification-drawer').exists()).toBe(true)
    expect(wrapper.find('.overlay').exists()).toBe(true)
  })

  it('applies correct classes when open', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    const drawer = wrapper.find('.notification-drawer')
    expect(drawer.classes()).toContain('w-96')
    expect(drawer.classes()).toContain('translate-x-0')
  })

  it('applies correct classes when closed', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: false }
    })

    const drawer = wrapper.find('.notification-drawer')
    expect(drawer.classes()).toContain('w-96')
    expect(drawer.classes()).toContain('translate-x-full')
  })

  it('renders header with title and close button', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    expect(wrapper.find('.drawer-header').exists()).toBe(true)
    expect(wrapper.find('.drawer-title').text()).toBe('Notifiche')
    expect(wrapper.find('.drawer-close').exists()).toBe(true)
  })

  it('renders footer with mark all read button', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    expect(wrapper.find('.drawer-footer').exists()).toBe(true)
    expect(wrapper.find('.mark-all-read').text()).toBe('Segna tutte come lette')
  })

  it('renders default content when no slot provided', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    expect(wrapper.find('.notification-item').exists()).toBe(true)
    expect(wrapper.text()).toContain('Nessuna notifica')
  })

  it('renders custom content in slot', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true },
      slots: {
        default: '<div class="custom-notification">Custom notification</div>'
      }
    })

    expect(wrapper.find('.custom-notification').exists()).toBe(true)
    expect(wrapper.text()).toContain('Custom notification')
  })

  it('emits close event when close button clicked', async () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    await wrapper.find('.drawer-close').trigger('click')
    expect(wrapper.emitted('close')).toBeTruthy()
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  it('emits close event when overlay clicked', async () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    await wrapper.find('.overlay').trigger('click')
    expect(wrapper.emitted('close')).toBeTruthy()
    expect(wrapper.emitted('close')).toHaveLength(1)
  })

  it('emits mark-all-read event when button clicked', async () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    await wrapper.find('.mark-all-read').trigger('click')
    expect(wrapper.emitted('mark-all-read')).toBeTruthy()
    expect(wrapper.emitted('mark-all-read')).toHaveLength(1)
  })

  it('applies correct styling to drawer', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    const drawer = wrapper.find('.notification-drawer')
    expect(drawer.classes()).toContain('fixed')
    expect(drawer.classes()).toContain('top-0')
    expect(drawer.classes()).toContain('right-0')
    expect(drawer.classes()).toContain('h-screen')
    expect(drawer.classes()).toContain('bg-gray-800')
    expect(drawer.classes()).toContain('border-l')
    expect(drawer.classes()).toContain('border-gray-700')
    expect(drawer.classes()).toContain('z-50')
    expect(drawer.classes()).toContain('transition-all')
    expect(drawer.classes()).toContain('duration-300')
    expect(drawer.classes()).toContain('flex')
    expect(drawer.classes()).toContain('flex-col')
  })

  it('applies correct styling to overlay', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: true }
    })

    const overlay = wrapper.find('.overlay')
    expect(overlay.classes()).toContain('fixed')
    expect(overlay.classes()).toContain('inset-0')
    expect(overlay.classes()).toContain('bg-black/50')
    expect(overlay.classes()).toContain('z-40')
  })

  it('does not render overlay when closed', () => {
    const wrapper = mount(NotificationDrawer, {
      props: { open: false }
    })

    expect(wrapper.find('.overlay').exists()).toBe(false)
  })
})
