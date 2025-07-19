<template>
  <div class="bg-gray-900 rounded-lg shadow-lg p-6 mt-8">
    <!-- Notifica -->
    <div v-if="notification.show" 
         :class="[
           'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300',
           notification.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
         ]">
      {{ notification.message }}
    </div>

    <div class="flex justify-between items-center border-b border-gray-700 pb-4 mb-6">
      <h1 class="text-2xl font-bold text-white">Gestione Utenti, Ruoli e Permessi</h1>
    </div>
    
    <!-- Indicatore di caricamento migliorato -->
    <div v-if="loading" class="flex flex-col items-center justify-center min-h-[300px]">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
      <span class="text-white text-lg font-semibold">Caricamento dati gestione permessi...</span>
      <p class="text-gray-400 mt-2">Preparazione dell'interfaccia di amministrazione</p>
    </div>
    
    <!-- Contenuto principale -->
    <div v-else>
      <!-- Statistiche rapide -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-gray-800 rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-blue-400">{{ props.users.length }}</div>
          <div class="text-gray-300 text-sm">Utenti Totali</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-green-400">{{ props.roles.length }}</div>
          <div class="text-gray-300 text-sm">Ruoli</div>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 text-center">
          <div class="text-2xl font-bold text-purple-400">{{ props.permissions.length }}</div>
          <div class="text-gray-300 text-sm">Permessi</div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <!-- Creazione nuovo utente -->
        <div class="bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-100">Crea nuovo utente</h2>
          <form @submit.prevent="createUser" class="space-y-4">
            <input v-model="newUser.name" placeholder="Nome" required class="w-full rounded border-gray-700 bg-gray-900 text-white" />
            <input v-model="newUser.email" placeholder="Email" required class="w-full rounded border-gray-700 bg-gray-900 text-white" />
            <input v-model="newUser.password" type="password" placeholder="Password" required class="w-full rounded border-gray-700 bg-gray-900 text-white" />
            <select v-model="newUser.role" required class="w-full rounded border-gray-700 bg-gray-900 text-white">
              <option value="" disabled>Seleziona ruolo</option>
              <option v-for="role in props.roles" :key="role.id" :value="role.name">{{ role.name }}</option>
            </select>
            <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 px-4 rounded transition">Crea utente</button>
          </form>
        </div>

        <!-- Creazione nuovo ruolo -->
        <div class="bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-xl font-semibold mb-4 text-gray-100">Crea nuovo ruolo</h2>
          <form @submit.prevent="createRole" class="flex gap-2">
            <input v-model="newRole" placeholder="Nome ruolo" required class="flex-1 rounded border-gray-700 bg-gray-900 text-white" />
            <button type="submit" class="bg-green-700 hover:bg-green-800 text-white font-semibold py-2 px-4 rounded transition">Crea ruolo</button>
          </form>
        </div>
      </div>

      <!-- Tabella utenti -->
      <div class="bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-100">Utenti</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Nome</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Email</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Ruolo</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Permessi</th>
                <th class="px-4 py-2"></th>
              </tr>
            </thead>
            <tbody class="bg-gray-800 divide-y divide-gray-700">
              <tr v-for="user in localUsers" :key="user.id">
                <td class="px-4 py-2 font-medium text-gray-100">{{ user.name }}</td>
                <td class="px-4 py-2 text-gray-300">{{ user.email }}</td>
                <td class="px-4 py-2">
                  <select v-model="user.role" class="rounded border-gray-700 bg-gray-900 text-white">
                    <option v-for="role in props.roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                  </select>
                </td>
                <td class="px-4 py-2">
                  <div class="flex flex-wrap gap-2 items-center">
                    <div v-for="perm in props.permissions" :key="perm.id" class="flex items-center gap-1">
                      <input type="checkbox" :value="perm.name" v-model="user.permissions" class="rounded border-gray-700 bg-gray-900" />
                      <span class="text-xs text-gray-300">{{ perm.name }}</span>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-2">
                  <button @click="saveUser(user)" class="bg-blue-700 hover:bg-blue-800 text-white px-3 py-1 rounded text-xs font-semibold transition">Salva modifiche</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watchEffect } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  users: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
  permissions: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false },
})

const newUser = ref({
  name: '',
  email: '',
  password: '',
  role: '',
})

const newRole = ref('')
const notification = ref({ show: false, message: '', type: 'success' })

// Copia locale degli utenti per evitare mutazioni dirette delle props
const localUsers = ref([])

watchEffect(() => {
  localUsers.value = (props.users || []).map(user => ({
    ...user,
    role: user.roles && user.roles.length ? user.roles[0].name : '',
    permissions: (user.permissions || []).map(p => p.name)
  }))
})

function showNotification(message, type = 'success') {
  notification.value = { show: true, message, type }
  setTimeout(() => {
    notification.value.show = false
  }, 3000)
}

function createUser() {
  router.post(route('admin.users.store'), newUser.value, { 
    preserveState: true, 
    preserveScroll: true, 
    replace: true,
    onSuccess: () => {
      showNotification('Utente creato con successo!', 'success')
      // Reset form
      newUser.value = { name: '', email: '', password: '', role: '' }
    },
    onError: (errors) => {
      showNotification('Errore nella creazione dell\'utente: ' + Object.values(errors).join(', '), 'error')
    }
  })
}

function createRole() {
  router.post(route('admin.roles.store'), { name: newRole.value }, { 
    preserveState: true, 
    preserveScroll: true, 
    replace: true,
    onSuccess: () => {
      showNotification('Ruolo creato con successo!', 'success')
      newRole.value = ''
    },
    onError: (errors) => {
      showNotification('Errore nella creazione del ruolo: ' + Object.values(errors).join(', '), 'error')
    }
  })
}

function saveUser(user) {
  // Aggiorna ruolo e permessi con una sola azione
  router.post(route('admin.users.updateRole', user.id), { role: user.role }, {
    preserveState: true, 
    preserveScroll: true, 
    onSuccess: () => {
      router.post(route('admin.users.updatePermissions', user.id), { permissions: user.permissions }, { 
        preserveState: true, 
        preserveScroll: true,
        onSuccess: () => {
          showNotification('Modifiche salvate con successo!', 'success')
        },
        onError: (errors) => {
          showNotification('Errore nel salvataggio dei permessi: ' + Object.values(errors).join(', '), 'error')
        }
      })
    },
    onError: (errors) => {
      showNotification('Errore nel salvataggio del ruolo: ' + Object.values(errors).join(', '), 'error')
    }
  })
}
</script> 