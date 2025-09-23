<script setup>
import { defineProps, defineEmits, ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

import SidebarContainer from '@/Components/Sidebar/SidebarContainer.vue';
import SidebarLogo from '@/Components/Sidebar/SidebarLogo.vue';
import SidebarSection from '@/Components/Sidebar/SidebarSection.vue';
import SidebarItem from '@/Components/Sidebar/SidebarItem.vue';
import ApplicationLogo from '@/Components/Items/ApplicationLogo.vue';
import Topbar from '@/Components/Topbar/Topbar.vue';
import NotificationDrawer from '@/Components/Drawer/NotificationDrawer.vue';

// Import profile component
import ProfileComponent from '@/Pages/Profile/Edit.vue';

const props = defineProps({
    activeTab: String,
    mustVerifyEmail: {
        type: Boolean,
        default: false
    },
    status: {
        type: String,
        default: ''
    }
});
const emit = defineEmits(['change-tab']);
const page = usePage();

const collapsed = ref(false);
const notificationOpen = ref(false);
const notificationsCount = ref(0);

const user = ref(page.props.auth?.user ?? null);

const unsubscribe = router.on('success', (event) => {
    if (event.detail.page.props.auth?.user) {
        user.value = event.detail.page.props.auth.user;
    }
});

function go(tab) {
    emit('change-tab', tab);
}

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen bg-gray-900 text-gray-100 flex w-full">
        <!-- Sidebar -->
        <SidebarContainer :collapsed="collapsed">
            <SidebarLogo :collapsed="collapsed" @toggle="collapsed = !collapsed">
                <template #icon>
                    <ApplicationLogo class="h-9 w-9 object-contain" />
                </template>
                <template #expand-icon>
                    <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                </template>
                <template #collapse-icon>
                    <i class="fas fa-chevron-left text-gray-400 text-xs"></i>
                </template>
            </SidebarLogo>

            <nav class="sidebar-nav px-3 py-4 flex-1 overflow-y-auto overflow-x-hidden">
                <SidebarSection title="Principale" :collapsed="collapsed">
                    <SidebarItem :active="props.activeTab === 'home'" :collapsed="collapsed" @click="go('home')">
                        <template #icon>
                            <i class="fas fa-home"></i>
                        </template>
                        Dashboard
                    </SidebarItem>
                    <SidebarItem :active="props.activeTab === 'progetti'" :collapsed="collapsed" badge="" @click="go('projects')">
                        <template #icon>
                            <i class="fas fa-project-diagram"></i>
                        </template>
                        Progetti
                    </SidebarItem>
                    <SidebarItem :active="props.activeTab === 'deadlines'" :collapsed="collapsed" badge="" @click="go('deadlines')">
                        <template #icon>
                            <i class="fas fa-calendar-day"></i>
                        </template>
                        Deadlines
                    </SidebarItem>
                    <SidebarItem :active="props.activeTab === 'membri'" :collapsed="collapsed" @click="go('membri')">
                        <template #icon>
                            <i class="fas fa-users"></i>
                        </template>
                        Gestisci membri
                    </SidebarItem>
                </SidebarSection>

                <SidebarSection title="Strumenti" :collapsed="collapsed">
                    <SidebarItem :active="props.activeTab === 'task'" :collapsed="collapsed" @click="go('task')">
                        <template #icon>
                            <i class="fas fa-tasks"></i>
                        </template>
                        Task Board
                    </SidebarItem>
                    <SidebarItem :active="props.activeTab === 'report'" :collapsed="collapsed" @click="go('report')">
                        <template #icon>
                            <i class="fas fa-chart-line"></i>
                        </template>
                        Report
                    </SidebarItem>
                    <SidebarItem :active="props.activeTab === 'chat'" :collapsed="collapsed" badge="" @click="go('chat')">
                        <template #icon>
                            <i class="fas fa-comments"></i>
                        </template>
                        Chat
                    </SidebarItem>
                </SidebarSection>

                <SidebarSection title="Amministrazione" :collapsed="collapsed">
                    <SidebarItem :active="props.activeTab === 'impostazioni'" :collapsed="collapsed" @click="go('impostazioni')">
                        <template #icon>
                            <i class="fas fa-cog"></i>
                        </template>
                        Impostazioni
                    </SidebarItem>
                    <SidebarItem
                        v-if="user && user.hasManagementPermissions"
                        :active="props.activeTab === 'permessi'"
                        :collapsed="collapsed"
                        @click="go('permessi')"
                    >
                        <template #icon>
                            <i class="fas fa-shield-alt"></i>
                        </template>
                        Permessi
                    </SidebarItem>
                    <SidebarItem 
                        :active="props.activeTab === 'profile'" 
                        :collapsed="collapsed" 
                        @click="go('profile')"
                    >
                        <template #icon>
                            <i class="fas fa-user"></i>
                        </template>
                        Profilo
                    </SidebarItem>
                </SidebarSection>
            </nav>

            <div class="sidebar-footer px-3 py-4 border-t border-gray-700">
                <div class="user-profile flex items-center px-2 py-2 rounded-xl hover:bg-gray-700/60" :class="{ 'justify-center': collapsed }"  @click="go('profile')">
                    <div class="user-avatar w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-[#07b4f6] flex items-center justify-center font-semibold" :class="{ 'mr-3': !collapsed }">
                        {{ user?.name?.substring(0,1) ?? 'U' }}
                    </div>
                    <div class="user-info min-w-0" v-if="!collapsed">
                        <div class="user-name text-sm font-medium truncate">{{ user?.name }}</div>
                        <div class="user-role text-xs text-gray-400 truncate">{{ user?.email }}</div>
                    </div>
                </div>
                <div v-if="!collapsed" class="mt-3 space-y-2">
                    <button type="button" class="block text-left text-sm text-red-400 hover:text-red-300" @click="logout">
                        <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                    </button>
                </div>
            </div>
        </SidebarContainer>

        <!-- Main Content -->
        <div class="main-content flex-1 min-w-0 flex flex-col">
            <Topbar :notifications="notificationsCount" @toggle-notifications="notificationOpen = true" />

            <header class="bg-gray-800 border-b border-gray-700" v-if="props.activeTab === 'profile'">
                <div class="px-4 py-6 sm:px-6 lg:px-8 w-full">
                    <h2 class="text-2xl font-bold text-white">Profilo Utente</h2>
                    <p class="mt-1 text-sm text-gray-300">Gestisci le informazioni del tuo profilo</p>
                </div>
            </header>

            <main class="content-area flex-1 p-6 overflow-y-auto">
                <!-- Contenuto dinamico in base alla tab attiva -->
                <div v-if="props.activeTab === 'home'">
                    <slot />
                </div>
                
                <div v-else-if="props.activeTab === 'profile'" class="max-w-7xl mx-auto space-y-6">
                   <ProfileComponent></ProfileComponent>
                </div>

                <div v-else-if="props.activeTab === 'projects'" class="max-w-4xl mx-auto">
                   
                </div>
                
                <div v-else>
                    <div class="text-center py-12">
                        <i class="fas fa-cogs text-6xl text-gray-600 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-300">Sezione in sviluppo</h3>
                        <p class="text-gray-500 mt-2">Questa funzionalità sarà presto disponibile</p>
                    </div>
                </div>
            </main>
        </div>

        <NotificationDrawer :open="notificationOpen" @close="notificationOpen = false" @mark-all-read="notificationsCount = 0" />
    </div>
</template>