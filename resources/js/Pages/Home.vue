<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserManagementPanel from '@/Components/Admin/UserManagementPanel.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';

const page = usePage();
const activeTab = ref('home');
const localUsers = ref([]);
const localRoles = ref([]);
const localPermissions = ref([]);
const loadingPanel = ref(false);

// Controlla se l'utente ha i permessi per gestire utenti/ruoli
const hasManagementPermissions = computed(() => {
  const user = page.props.auth.user;
  return user && user.hasManagementPermissions;
});

// Inizializza i dati dai props del middleware se disponibili
const initializeDataFromProps = () => {
  if (page.props.managementData) {
    localUsers.value = page.props.managementData.users || [];
    localRoles.value = page.props.managementData.roles || [];
    localPermissions.value = page.props.managementData.permissions || [];
  }
};

async function loadPermData() {
  // Se i dati sono già disponibili dai props, non fare la chiamata API
  if (page.props.managementData) {
    initializeDataFromProps();
    return;
  }

  loadingPanel.value = true;
  try {
    const res = await fetch('/api/admin/perm-management-data');
    if (!res.ok) throw new Error('Errore caricamento dati gestione permessi');
    const data = await res.json();
    localUsers.value = data.users || [];
    localRoles.value = data.roles || [];
    localPermissions.value = data.permissions || [];
  } catch (e) {
    console.error('Errore nel caricamento dati gestione permessi:', e);
    alert('Errore nel caricamento dati gestione permessi');
  } finally {
    loadingPanel.value = false;
  }
}

// Carica i dati all'avvio se l'utente ha i permessi
onMounted(() => {
  if (hasManagementPermissions.value) {
    initializeDataFromProps();
    // Se i dati non sono disponibili dai props, caricali via API
    if (localUsers.value.length === 0) {
      loadPermData();
    }
  }
});

// Carica i dati quando si passa al tab permessi (fallback)
watch(
  () => activeTab.value,
  (newVal) => {
    if (newVal === 'permessi' && hasManagementPermissions.value && localUsers.value.length === 0) {
      loadPermData();
    }
  }
);
</script>

<template>
    <Head title="Home" />
    <AuthenticatedLayout
      :activeTab="activeTab"
      @change-tab="tab => { activeTab = tab }"
    >
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                Benvenuto in TeamPulse
            </h2>
        </template>
        
        <!-- Tab Home -->
        <div v-if="activeTab === 'home'" class="flex flex-col items-center justify-center w-full min-h-[60vh]">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 max-w-xl w-full text-center">
                <h1 class="text-3xl font-bold mb-4 text-gray-900 dark:text-white">Ciao e benvenuto 👋</h1>
                <p class="text-lg text-gray-700 dark:text-gray-300">
                    Questa è la tua homepage personale di TeamPulse.<br>
                    Da qui potrai accedere a tutte le funzionalità dell'applicazione.
                </p>
                
                <!-- Mostra informazioni sui permessi dell'utente -->
                <div v-if="hasManagementPermissions" class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">
                        Permessi di Amministrazione
                    </h3>
                    <p class="text-blue-700 dark:text-blue-300">
                        Hai accesso alla gestione utenti e permessi. 
                        Usa il tab "Gestione permessi" per amministrare il sistema.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Tab Gestione Permessi -->
        <div v-else-if="activeTab === 'permessi'" class="mt-10">
            <UserManagementPanel
                :users="localUsers"
                :roles="localRoles"
                :permissions="localPermissions"
                :loading="loadingPanel"
            />
        </div>
        
        <!-- Tab sconosciuta -->
        <div v-else>
            <div class="text-red-500">Tab sconosciuta: {{ activeTab }}</div>
        </div>
    </AuthenticatedLayout>
</template> 