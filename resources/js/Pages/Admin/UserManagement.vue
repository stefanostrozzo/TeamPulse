<template>
  <div class="max-w-7xl mx-auto py-10 px-4 dark bg-gray-900 min-h-screen">
    <h1 class="text-3xl font-bold mb-8 text-gray-100">Gestione Utenti, Ruoli e Permessi</h1>

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
            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
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
            <tr v-for="user in users" :key="user.id">
              <td class="px-4 py-2 font-medium text-gray-100">{{ user.name }}</td>
              <td class="px-4 py-2 text-gray-300">{{ user.email }}</td>
              <td class="px-4 py-2">
                <select v-model="user.role" class="rounded border-gray-700 bg-gray-900 text-white">
                  <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                </select>
              </td>
              <td class="px-4 py-2">
                <div class="flex flex-wrap gap-2 items-center">
                  <div v-for="perm in permissions" :key="perm.id" class="flex items-center gap-1">
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
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  users: Array,
  roles: Array,
  permissions: Array,
})

const newUser = ref({
  name: '',
  email: '',
  password: '',
  role: '',
})

const newRole = ref('')

function createUser() {
  router.post(route('admin.users.store'), newUser.value)
}

function createRole() {
  router.post(route('admin.roles.store'), { name: newRole.value })
}

function saveUser(user) {
  // Aggiorna ruolo e permessi con una sola azione
  router.post(route('admin.users.updateRole', user.id), { role: user.role }, {
    onSuccess: () => {
      router.post(route('admin.users.updatePermissions', user.id), { permissions: user.permissions })
    }
  })
}

// Inizializza i ruoli e permessi selezionati per ogni utente
props.users.forEach(user => {
  user.role = user.roles.length ? user.roles[0].name : ''
  user.permissions = user.permissions.map(p => p.name)
})
</script> 