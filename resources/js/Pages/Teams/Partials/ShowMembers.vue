<script setup>
import { computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

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

const emit = defineEmits(['back']);
const page = usePage();

// Form for inviting a new member
const inviteForm = useForm({
    email: '',
    role: 'member', // Default role for new invitations
});

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
                title: 'Inviato!',
                text: 'Invito spedito con successo.',
                icon: 'success',
                iconColor: '#10b981',
                background: '#1f2937',
                color: '#ffffff',
                confirmButtonColor: '#07b4f6',
                confirmButtonText: 'Ottimo',
            });
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
};

/**
 * Remove a member from the team
 * @param {Object} user - The user to remove
 */
const removeMember = (user) => {
    Swal.fire({
        title: 'Sei sicuro?',
        text: `Rimuoverai ${user.name} dal team.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Sì, rimuovi',
        cancelButtonText: 'Annulla',
        background: '#1f2937',
        color: '#ffffff',
    }).then((result) => {
        if (result.isConfirmed) {
            // Logic for removal: uses the user ID but usually targets a pivot table
            router.delete(route('teams.members.remove', {
                user: user.id,
                team: props.team.id
            }), {
                preserveScroll: true,
                onSuccess: () => Swal.fire({
                    title: 'Rimosso!',
                    text: 'Membro rimosso con successo.',
                    icon: 'success',
                    iconColor: '#10b981',
                    background: '#1f2937',
                    color: '#ffffff',
                    confirmButtonColor: '#07b4f6',
                    confirmButtonText: 'Ottimo',
                })
            });
        }
    });
};

// Check if current user can manage this team (owner or superadmin)
const canManage = computed(() => {
    const currentUser = page.props.auth.user;

    if (currentUser.hasManagementPermissions) return true;

    const userInTeam = props.team.users?.find(u => u.id === currentUser.id);

    return userInTeam?.pivot?.role === 'owner' || userInTeam?.pivot?.role === 'manager';
});

</script>

<template>
    <div class="show-members-container space-y-8">
        <div class="flex items-center justify-between border-b border-gray-800 pb-5">
            <div class="flex items-center gap-4">
                <button
                    @click="$emit('back')"
                    class="p-2 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white transition-all"
                >
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight">{{ team.name }}</h2>
                    <p class="text-sm text-gray-400">Gestione membri e permessi del team</p>
                </div>
            </div>

            <div v-if="canManage" class="flex gap-2">
                <span class="bg-[#07b4f6]/10 text-[#07b4f6] text-xs font-bold px-3 py-1 rounded-full uppercase">
                    Admin Mode
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                <h3 class="text-lg font-semibold text-white mb-4">Membri del Team ({{ team.users?.length || 0 }})</h3>

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
                        <span class="text-[10px] px-2 py-1 rounded bg-gray-900 text-gray-400 uppercase font-bold border border-gray-700">
                            {{ user.pivot?.role || 'Member' }}
                        </span>

                        <button
                            v-if="canManage && user.id !== $page.props.auth.user.id"
                            @click="removeMember(user)"
                            class="text-gray-500 hover:text-red-400 transition-colors p-2"
                            title="Rimuovi dal team"
                        >
                            <i class="fas fa-user-minus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="canManage" class="space-y-6">
                <div class="p-6 bg-gray-800 border border-gray-700 rounded-2xl shadow-xl">
                    <h3 class="text-lg font-semibold text-white mb-2">Invita Collaboratore</h3>
                    <p class="text-sm text-gray-400 mb-6">Invia un invito via email per farli entrare nel team.</p>

                    <form @submit.prevent="sendInvitation" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Email</label>
                            <input
                                v-model="inviteForm.email"
                                type="email"
                                required
                                placeholder="collega@azienda.it"
                                class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-[#07b4f6] outline-none"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Ruolo</label>
                            <select
                                v-model="inviteForm.role"
                                class="w-full bg-gray-900 border border-gray-700 rounded-xl px-4 py-2 text-white focus:ring-2 focus:ring-[#07b4f6] outline-none"
                            >
                                <option value="guest">Ospite</option>
                                <option value="member">Membro</option>
                                <option value="manager">Manager</option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            :disabled="inviteForm.processing"
                            class="w-full py-3 bg-[#07b4f6] text-white font-bold rounded-xl hover:bg-[#06a3dd] transition-all disabled:opacity-50"
                        >
                            <i v-if="inviteForm.processing" class="fas fa-spinner fa-spin mr-2"></i>
                            Invia Invito
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
