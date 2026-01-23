<script setup>
import { ref, computed, onMounted } from 'vue';
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
import ProfileEdit from '@/Pages/Profile/Edit.vue';
import ProjectIndex from '@/Pages/Projects/Index.vue';
import DashboardIndex from '@/Pages/Dashboard/Index.vue';
import TeamIndex from '@/Pages/Teams/Index.vue';

const page = usePage();
const collapsed = ref(false);
const notificationOpen = ref(false);
const showCreateTeamModal = ref(false);

// We read the current tab from the URL to highlight the sidebar icons
const currentTab = computed(() => page.props.activeTab || 'dashboard');

const selectedProjectIdFromDashboard = ref(null);
const selectedTeamIdFromDashboard = ref(null);
const selectedTaskIdFromSearch = ref(null);
const selectedMemberIdFromSearch = ref(null);

const goToSearchResult = (data) => {
    const { type, id, projectId } = data;

    selectedProjectIdFromDashboard.value = null;
    selectedTeamIdFromDashboard.value = null;
    selectedTaskIdFromSearch.value = null;
    selectedMemberIdFromSearch.value = null;

    if (type === 'project') {
        selectedProjectIdFromDashboard.value = id;
        navigateTo('projects');
    }
    else if (type === 'team') {
        selectedTeamIdFromDashboard.value = id;
        navigateTo('teams');
    }
    else if (type === 'task') {
        selectedProjectIdFromDashboard.value = projectId;
        selectedTaskIdFromSearch.value = id;
        navigateTo('projects');
    }
    else if (type === 'member') {
        selectedMemberIdFromSearch.value = id;
        selectedTeamIdFromDashboard.value = page.props.auth.user.current_team_id;
        navigateTo('teams');
    }
};

const goToProjectDetail = (id) => {
    selectedProjectIdFromDashboard.value = id;
    navigateTo('projects');
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
function navigateTo(tabName) {
    router.get(route('home'), { tab: tabName }, {
        preserveState: true,
        preserveScroll: true
    });
}

function logout() {
    router.post(route('logout'));
}

/**
 * If the user doesn't have a team, redirects to team page to create it
 */
onMounted(() => {
    if (page.props.teamsCount === 0 && page.props.activeTab !== 'teams') {
        showCreateTeamModal.value = true;
        navigateTo('teams');
    }
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
            <Topbar @toggle-sidebar="collapsed = !collapsed" @open-notifications="notificationOpen = true"
                @search-navigation="goToSearchResult"
                :notifications="0"
            />

            <main class="flex-1 p-6 overflow-y-auto bg-gray-50 dark:bg-gray-950">
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
                            :initialProjectId="selectedProjectIdFromDashboard"
                            :highlight-task-id="selectedTaskIdFromSearch"
                            @clear-task-highlight="selectedTaskIdFromSearch = null"
                        />
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

    </div>
</template>
