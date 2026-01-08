<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import TextInput from '@/Components/Items/TextInput.vue';

/**
 * Component Props
 * @property {Object} team - The selected team data (must include users)
 */
const props = defineProps({
    team: {
        type: Object,
        required: true
    }
});

console.log(props.team);

/**
 * Role Translation Map
 * Maps database role keys to their Italian display names
 */
const roleLabels = {
    owner: 'Proprietario',
    manager: 'Manager',
    member: 'Membro',
    guest: 'Ospite'
};

const emit = defineEmits(['back']);
const page = usePage();

/**
 * Role Management State
 * 'roleUpdates' tracks changes locally before submission
 */
const roleUpdates = ref({});
const hasRoleChanges = ref(false);

const inviteForm = useForm({
    email: '',
    role: 'member',
});

/**
 * Authorization Check
 * Determines if the current user can manage roles and invitations
 */
const canManage = computed(() => {
    const currentUser = page.props.auth.user;
    if (currentUser.hasManagementPermissions) return true;

    const userInTeam = props.team.users?.find(u => u.id === currentUser.id);
    return userInTeam?.pivot?.role === 'owner' || userInTeam?.pivot?.role === 'manager';
});

/**
 * Stage a role change locally
 * @param {Number} userId
 * @param {String} newRole
 */
const stageRoleChange = (userId, newRole) => {
    roleUpdates.value[userId] = newRole;
    hasRoleChanges.value = true;
};

/**
 * Persist role changes to the server
 * Iterates through staged updates and sends PUT requests
 */
const saveRoleChanges = () => {
    const updates = roleUpdates.value;

    router.put(route('teams.members.updateRole', { team: props.team.id }),
        {
            updates: updates
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                hasRoleChanges.value = false;
                roleUpdates.value = {};
                Swal.fire({
                    title: 'Aggiornati!',
                    text: 'Tutti i ruoli sono stati sincronizzati correttamente.',
                    icon: 'success',
                    background: '#1f2937',
                    color: '#ffffff',
                    confirmButtonColor: '#07b4f6',
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Errore',
                    text: 'Si è verificato un problema durante l\'aggiornamento.',
                    icon: 'error',
                    background: '#1f2937',
                    color: '#ffffff',
                });
            }
        });
};

/**
 * Handle team invitation submission
 */
const sendInvitation = () => {
    inviteForm.transform((data) => ({
        ...data,
        team_id: props.team.id,
    })).post(route('teams.invite'), {
        onSuccess: () => {
            inviteForm.reset();
            Swal.fire({
                title: 'Inviata!',
                text: 'Invito spedito con successo.',
                icon: 'success',
                background: '#1f2937',
                color: '#ffffff',
                confirmButtonColor: '#07b4f6',
            });
        }
    });
};

/**
 * Remove a member from the team
 */
const removeMember = (user) => {
    Swal.fire({
        title: 'Sicuro?',
        text: `Stai per rimuovere ${user.name} dal team.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Si, Rimuovi',
        background: '#1f2937',
        color: '#ffffff',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('teams.members.remove', {
                user: user.id,
                team: props.team.id
            }), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Rimosso!',
                        text: 'Membro rimosso con successo!.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#1f2937',
                        color: '#ffffff',
                    });
                },
            });
        }
    });
};
</script>

<template>
    <div class="show-members-container space-y-8">
        <div class="flex items-center justify-between border-b border-gray-800 pb-5">
            <div class="flex items-center gap-4">
                <button @click="$emit('back')" class="p-2 hover:bg-gray-800 rounded-lg text-gray-400 transition-all">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">{{ team.name }}</h2>
                    <p class="text-sm text-gray-400">Visualizza e gestisci il tuo team</p>
                </div>
            </div>
            <div v-if="canManage" class="flex gap-2">
                <span class="bg-[#07b4f6]/10 text-[#07b4f6] text-xs font-bold px-3 py-1 rounded-full uppercase">
                    Amministratore
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <h3 class="text-lg font-semibold text-white mb-4">Membri ({{ team.users?.length || 0 }})</h3>

                <div v-for="user in team.users" :key="user.id"
                     class="flex items-center justify-between p-4 bg-gray-800/50 border border-gray-700 rounded-xl hover:border-gray-600 transition-all"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-[#07b4f6]/20 flex items-center justify-center text-[#07b4f6] font-bold">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-white font-medium">{{ user.name }}</p>
                            <p class="text-xs text-gray-500">{{ user.email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <template v-if="canManage">
                            <select
                                @change="stageRoleChange(user.id, $event.target.value)"
                                class="bg-gray-900 border border-gray-700 text-[10px] px-2 py-1 rounded text-[#07b4f6] uppercase font-bold outline-none focus:ring-1 focus:ring-[#07b4f6]"
                            >
                                <option
                                    v-for="(label, key) in roleLabels"
                                    :key="key"
                                    :value="key"
                                    :selected="user.pivot?.role === key"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </template>
                        <template v-else>
                            <span class="text-[10px] px-2 py-1 rounded bg-gray-900 text-gray-400 uppercase font-bold border border-gray-700">
                                {{ roleLabels[user.pivot?.role] || 'Membro' }}
                            </span>
                        </template>
                        <button
                            v-if="canManage && user.id !== $page.props.auth.user.id"
                            @click="removeMember(user)"
                            class="text-gray-500 hover:text-red-400 transition-colors p-2"
                        >
                            <i class="fas fa-user-minus"></i>
                        </button>
                    </div>
                </div>

                <div v-if="hasRoleChanges" class="flex justify-end pt-4">
                    <button
                        @click="saveRoleChanges"
                        class="px-6 py-2 bg-[#07b4f6] text-white font-bold rounded-xl hover:bg-[#06a3dd] transition-all shadow-lg shadow-[#07b4f6]/20"
                    >
                        Salva i nuovi ruoli
                    </button>
                </div>
            </div>

            <div v-if="canManage" class="space-y-6">
                <div class="p-6 bg-gray-800 border border-gray-700 rounded-2xl shadow-xl">
                    <h3 class="text-lg font-semibold text-white mb-2">Invita un collaboratore</h3>
                    <p class="text-sm text-gray-400 mb-6">Manda una mail di invito per entrare a fare parte del team.</p>

                    <form @submit.prevent="sendInvitation" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Email</label>
                            <TextInput
                                v-model="inviteForm.email"
                                type="email"
                                required
                                class="w-full"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Role</label>
                            <select
                                v-model="inviteForm.role"
                                class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-[#07b4f6] outline-none"
                            >
                                <option v-for="(label, key) in roleLabels" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            :disabled="inviteForm.processing"
                            class="w-full py-3 bg-[#07b4f6] text-white font-bold rounded-xl hover:bg-[#06a3dd] transition-all"
                        >
                            <i v-if="inviteForm.processing" class="fas fa-spinner fa-spin mr-2"></i>
                            Manda invito
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
