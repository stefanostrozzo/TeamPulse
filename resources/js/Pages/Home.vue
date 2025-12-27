<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

// UI Components
import SidebarContainer from '@/Components/Sidebar/SidebarContainer.vue';
import SidebarLogo from '@/Components/Sidebar/SidebarLogo.vue';
import SidebarSection from '@/Components/Sidebar/SidebarSection.vue';
import SidebarItem from '@/Components/Sidebar/SidebarItem.vue';
import ApplicationLogo from '@/Components/Items/ApplicationLogo.vue';
import Topbar from '@/Components/Topbar/Topbar.vue';
import NotificationDrawer from '@/Components/Drawer/NotificationDrawer.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserManagementPanel from '@/Components/Admin/UserManagementPanel.vue';
import ProfileEdit from '@/Pages/Profile/Edit.vue';

const page = usePage();
const collapsed = ref(false);
const notificationOpen = ref(false);
const user = computed(() => page.props.auth?.user ?? null);

// We read the current tab from the URL to highlight the sidebar icons
const currentTab = computed(() => page.props.activeTab || 'dashboard');

/**
 * Navigate using Inertia router.
 */
function navigateTo(tabName) {
    router.get(route('home'), { tab: tabName }, {
        preserveState: true,
        preserveScroll: true
    });
}

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="flex h-screen bg-gray-900 text-white overflow-hidden">

        <SidebarContainer :collapsed="collapsed">
            <SidebarLogo :collapsed="collapsed" @toggle="collapsed = !collapsed">
                <template #icon>
                    <ApplicationLogo class="h-9 w-9 object-contain" />
                </template>
            </SidebarLogo>

            <nav class="sidebar-nav px-3 py-4 flex-1 overflow-y-auto overflow-x-hidden">
                <SidebarSection title="Gestione progetto" :collapsed="collapsed">
                    <SidebarItem :active="currentTab === 'dashboard'" :collapsed="collapsed" @click="navigateTo('dashboard')">
                        <template #icon><i class="fas fa-home"></i></template>
                        Home
                    </SidebarItem>

                    <SidebarItem :active="currentTab === 'projects'" :collapsed="collapsed" @click="navigateTo('projects')">
                        <template #icon><i class="fas fa-project-diagram"></i></template>
                        Progetti
                    </SidebarItem>
                </SidebarSection>

                <SidebarSection title="Amministrazione" :collapsed="collapsed">
                    <SidebarItem
                        v-if="user?.hasManagementPermissions"
                        :active="currentTab === 'permissions'"
                        :collapsed="collapsed"
                        @click="navigateTo('permissions')"
                    >
                        <template #icon><i class="fas fa-shield-alt"></i></template>
                        Permissions
                    </SidebarItem>

                    <SidebarItem :active="currentTab === 'profile'" :collapsed="collapsed" @click="navigateTo('profile')">
                        <template #icon><i class="fas fa-user"></i></template>
                        Profilo
                    </SidebarItem>
                </SidebarSection>
            </nav>

            <div class="sidebar-footer px-3 py-4 border-t border-gray-700">
                <div class="user-profile flex items-center px-2 py-2 rounded-xl hover:bg-gray-700/60" :class="{ 'justify-center': collapsed }" @click="navigateTo('profile')">
                    <div class="user-avatar w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-[#07b4f6] flex items-center justify-center font-semibold">
                        {{ user?.name?.substring(0,1) }}
                    </div>
                    <div v-if="!collapsed" class="ml-3 min-w-0">
                        <div class="text-sm font-medium truncate">{{ user?.name }}</div>
                    </div>
                </div>
            </div>
        </SidebarContainer>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden text-gray-900 dark:text-gray-100">
            <Topbar @toggle-sidebar="collapsed = !collapsed" @open-notifications="notificationOpen = true" />

            <main class="flex-1 p-6 overflow-y-auto bg-gray-50 dark:bg-gray-950">
                <AuthenticatedLayout
                    :user="user"
                    @toggle-sidebar="collapsed = !collapsed"
                    @open-notifications="notificationOpen = true"
                >
                    <div v-if="currentTab === 'dashboard'">
                        <h1 class="text-2xl font-bold">Benvenuto, {{ user?.name }}</h1>
                    </div>

                    <div v-else-if="currentTab === 'projects'">
                        <h1 class="text-2xl font-bold">I tuoi Progetti</h1>
                    </div>

                    <div v-else-if="currentTab === 'profile'">
                        <ProfileEdit
                            :must-verify-email="page.props.mustVerifyEmail"
                            :status="page.props.status"
                        />
                    </div>

                    <div v-else-if="currentTab === 'permissions'">
                        <UserManagementPanel
                            v-if="page.props.managementData"
                            :users="page.props.managementData.users"
                        />
                    </div>
                </AuthenticatedLayout>
            </main>
        </div>

        <NotificationDrawer :open="notificationOpen" @close="notificationOpen = false" />

    </div>
</template>
