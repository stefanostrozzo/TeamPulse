<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useMessagingStore } from '@/stores/messagingStore';
import { storeToRefs } from 'pinia';
import Toast from 'primevue/toast';

// UI Components
import SidebarContainer from '@/Components/Sidebar/SidebarContainer.vue';
import SidebarLogo from '@/Components/Sidebar/SidebarLogo.vue';
import SidebarSection from '@/Components/Sidebar/SidebarSection.vue';
import SidebarItem from '@/Components/Sidebar/SidebarItem.vue';
import ApplicationLogo from '@/Components/Items/ApplicationLogo.vue';
import Topbar from '@/Components/Topbar/Topbar.vue';
import NotificationDrawer from '@/Components/Drawer/NotificationDrawer.vue';
import UnreadMessagesDrawer from '@/Components/Drawer/UnreadMessagesDrawer.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MessagingPanel from '@/Components/Messaging/MessagingPanel.vue';
import ProfileEdit from '@/Pages/Profile/Edit.vue';
import ProjectIndex from '@/Pages/Projects/Index.vue';
import DashboardIndex from '@/Pages/Dashboard/Index.vue';
import TeamIndex from '@/Pages/Teams/Index.vue';

const page = usePage();
const collapsed = ref(false);
const notificationOpen = ref(false);
const messagesOpen = ref(false);
const showCreateTeamModal = ref(false);

const toast = useToast();
const store = useMessagingStore();
const { newIncomingMessage } = storeToRefs(store);

const quickReplyText = ref({});

// We read the current tab from the URL to highlight the sidebar icons
const currentTab = computed(() => page.props.activeTab || 'dashboard');

// Project / team / search-driven selection state
// The initial project can come from the backend (session-persisted)
const initialProjectId = computed(() => page.props.initialProjectId || null);
const selectedTeamIdFromDashboard = ref(null);
const selectedTaskIdFromSearch = ref(null);
const selectedMemberIdFromSearch = ref(null);

const goToSearchResult = (data) => {
    const { type, id, projectId } = data;

    selectedTeamIdFromDashboard.value = null;
    selectedTaskIdFromSearch.value = null;
    selectedMemberIdFromSearch.value = null;

    if (type === 'project') {
        navigateTo('projects', { initialProjectId: id });
    }
    else if (type === 'team') {
        selectedTeamIdFromDashboard.value = id;
        navigateTo('teams');
    }
    else if (type === 'task') {
        selectedTaskIdFromSearch.value = id;
        navigateTo('projects', { initialProjectId: projectId });
    }
    else if (type === 'member') {
        selectedMemberIdFromSearch.value = id;
        selectedTeamIdFromDashboard.value = page.props.auth.user.current_team_id;
        navigateTo('teams');
    }
};

const goToProjectDetail = (id) => {
    navigateTo('projects', { initialProjectId: id });
};

/**
 * Handles navigation from Dashboard to the Teams section.
 * Updates state and triggers an Inertia router visit to maintain SPA consistency.
 * * @param {Number|null} id - The specific Team ID to focus on, if any.
 */
const goToTeamDetail = (id) => {
    selectedTeamIdFromDashboard.value = id;
    navigateTo('teams');
};

/**
 * Navigate using Inertia router.
 */
function navigateTo(tabName, extraParams = {}) {
    router.get(route('home'), { tab: tabName, ...extraParams }, {
        preserveState: true,
        preserveScroll: true
    });
}

function handleNavigateToConversation(conversationId) {
    store.activeConversationId = conversationId;
    navigateTo('messaging');
}

watch(newIncomingMessage, (newVal) => {
    if (newVal) {
        toast.add({
            severity: 'info',
            summary: `Nuovo messaggio da ${newVal.message.sender?.name || 'Utente'}`,
            detail: newVal.message.body,
            life: 8000,
            group: 'message-toast',
            messageData: newVal
        });
    }
});

const sendQuickReply = async (conversationId, closeCallback) => {
    const text = quickReplyText.value[conversationId]?.trim();
    if (text) {
        await store.sendMessage(conversationId, text);
        quickReplyText.value[conversationId] = '';
    }
    if (closeCallback) closeCallback();
};

function logout() {
    router.post(route('logout'));
}

/**
 * If the user doesn't have a team, redirects to team page to create it
 */
onMounted(() => {
    // Initialize WebSockets and messaging state globally (idempotent — safe to call once).
    store.initialize(page.props.auth.user);

    if (page.props.teamsCount === 0 && page.props.activeTab !== 'teams') {
        showCreateTeamModal.value = true;
        navigateTo('teams');
    }
});

onUnmounted(() => {
    // Bug 9 fix: clean up all Echo subscriptions when the root layout unmounts
    store.cleanup();
});

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
                <SidebarSection title="Progetto" :collapsed="collapsed">
                    <SidebarItem :active="currentTab === 'dashboard'" :collapsed="collapsed" @click="navigateTo('dashboard')">
                        <template #icon><i class="fas fa-home"></i></template>
                        Home
                    </SidebarItem>

                    <SidebarItem :active="currentTab === 'teams'" :collapsed="collapsed" @click="navigateTo('teams')">
                        <template #icon><i class="fas fa-users"></i></template>
                        Teams
                    </SidebarItem>

                    <SidebarItem :active="currentTab === 'projects'" :collapsed="collapsed" @click="navigateTo('projects')">
                        <template #icon><i class="fas fa-project-diagram"></i></template>
                        Progetti
                    </SidebarItem>

                    <SidebarItem :active="currentTab === 'messaging'" :collapsed="collapsed" @click="navigateTo('messaging')">
                        <template #icon>
                            <div class="relative">
                                <i class="fas fa-comments"></i>
                                <span v-if="store.unreadCount > 0" class="absolute -top-1 -right-2 w-2 h-2 bg-[#07b4f6] rounded-full"></span>
                            </div>
                        </template>
                        Messaggi
                    </SidebarItem>
                </SidebarSection>

                <SidebarSection title="Amministrazione" :collapsed="collapsed">
                    <SidebarItem :active="currentTab === 'profile'" :collapsed="collapsed" @click="navigateTo('profile')">
                        <template #icon><i class="fas fa-user"></i></template>
                        Profilo
                    </SidebarItem>
                </SidebarSection>
            </nav>

            <div class="sidebar-footer px-3 py-4 border-t border-gray-700">
                <SidebarItem
                    :active="false"
                    :collapsed="collapsed"
                    @click="logout"
                    class="text-red-400 hover:text-red-300 hover:bg-red-400/10"
                >
                    <template #icon><i class="fas fa-sign-out-alt"></i></template>
                    Esci
                </SidebarItem>
            </div>
        </SidebarContainer>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden text-gray-900 dark:text-gray-100">
            <Topbar @toggle-sidebar="collapsed = !collapsed" @open-notifications="notificationOpen = true" @open-messages="messagesOpen = true"
                @search-navigation="goToSearchResult"
                :notifications="0"
            />

            <main class="flex-1 p-6 overflow-y-auto bg-gray-950">
                <AuthenticatedLayout
                    @toggle-sidebar="collapsed = !collapsed"
                    @open-notifications="notificationOpen = true"
                >
                    <div v-if="currentTab === 'dashboard'">
                        <DashboardIndex
                            @navigate-to-project="goToProjectDetail"
                            @navigate-to-team="goToTeamDetail"
                        />
                    </div>

                    <div v-else-if="currentTab === 'teams'">
                        <TeamIndex
                            :teams="page.props.userTeams"
                            :autoOpenCreate="showCreateTeamModal"
                            :initial-team-id="selectedTeamIdFromDashboard"
                            :highlight-member-id="selectedMemberIdFromSearch"
                            @clear-member-highlight="selectedMemberIdFromSearch = null"
                        />
                    </div>

                    <div v-else-if="currentTab === 'projects'">
                        <ProjectIndex
                            :projects="page.props.projects"
                            :projectStats="page.props.stats"
                            :current-team-id="page.props.currentTeamId"
                            :initial-project-id="initialProjectId"
                            :highlight-task-id="selectedTaskIdFromSearch"
                            @clear-task-highlight="selectedTaskIdFromSearch = null"
                        />
                    </div>

                    <div v-else-if="currentTab === 'messaging'" class="h-full">
                        <MessagingPanel />
                    </div>

                    <div v-else-if="currentTab === 'profile'">
                        <ProfileEdit
                            :must-verify-email="page.props.mustVerifyEmail"
                            :status="page.props.status"
                        />
                    </div>
                </AuthenticatedLayout>
            </main>
        </div>

        <NotificationDrawer :open="notificationOpen" @close="notificationOpen = false" />
        <UnreadMessagesDrawer :is-open="messagesOpen" @close="messagesOpen = false" @navigate="handleNavigateToConversation" />

    </div>

    <!-- Global Message Toast Notification -->
    <Toast position="bottom-right" group="message-toast" :pt="{ root: { class: 'w-80 md:w-96' }, content: { class: 'bg-gray-800 text-white border border-gray-700 rounded-xl shadow-2xl' }, icon: { class: 'hidden' }, detail: { class: 'w-full m-0' } }">
        <template #message="slotProps">
            <div class="flex flex-col gap-2 w-full pt-1">
                <div class="flex items-center gap-2">
                    <i class="fas fa-comment-dots text-[#07b4f6]"></i>
                    <span class="font-bold text-sm text-white">{{ slotProps.message.summary }}</span>
                </div>
                <div class="text-sm text-gray-300 line-clamp-2 leading-snug">{{ slotProps.message.detail }}</div>
                
                <div class="flex gap-2 mt-3" @click.stop v-if="slotProps.message.messageData">
                    <input 
                        type="text" 
                        v-model="quickReplyText[slotProps.message.messageData.conversationId]" 
                        class="flex-1 bg-gray-900 border border-gray-700 text-white text-xs rounded-lg px-3 py-1.5 focus:border-[#07b4f6] focus:ring-1 focus:ring-[#07b4f6] transition-colors"
                        placeholder="Rispondi..."
                        @keyup.enter="sendQuickReply(slotProps.message.messageData.conversationId, slotProps.closeCallback)"
                    />
                    <button 
                        @click="sendQuickReply(slotProps.message.messageData.conversationId, slotProps.closeCallback)"
                        class="bg-[#07b4f6] hover:bg-[#069acc] text-white p-1 rounded-lg transition-colors flex items-center justify-center w-8 shrink-0 focus:outline-none"
                    >
                        <i class="fas fa-paper-plane text-xs"></i>
                    </button>
                </div>
            </div>
        </template>
    </Toast>
</template>
