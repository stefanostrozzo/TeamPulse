<script setup>
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

/**
 * Component Props
 * @property {Array}   invitations - List of pending invitation objects for the team
 * @property {Boolean} canManage   - Whether the current user has permission to cancel invitations
 */
defineProps({
    invitations: {
        type: Array,
        default: () => [],
    },
    canManage: {
        type: Boolean,
        default: false,
    },
});

/**
 * Role translation map for Italian display labels.
 */
const roleLabels = {
    owner: 'Proprietario',
    manager: 'Manager',
    member: 'Membro',
    guest: 'Ospite',
};

/**
 * Format an ISO-8601 date string into a human-readable Italian relative time.
 *
 * @param {String} dateString - ISO-8601 formatted date
 * @returns {String} Relative time label (e.g. "2 ore fa")
 */
const timeAgo = (dateString) => {
    const now = new Date();
    const date = new Date(dateString);
    const diffMs = now - date;
    const diffMinutes = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMinutes < 1) return 'Adesso';
    if (diffMinutes < 60) return `${diffMinutes} min fa`;
    if (diffHours < 24) return `${diffHours} ${diffHours === 1 ? 'ora' : 'ore'} fa`;
    return `${diffDays} ${diffDays === 1 ? 'giorno' : 'giorni'} fa`;
};

/**
 * Prompt the user for confirmation, then cancel the specified invitation.
 *
 * @param {Object} invitation - The invitation object to cancel
 */
const cancelInvitation = (invitation) => {
    Swal.fire({
        title: 'Annullare l\'invito?',
        text: `L'invito per ${invitation.email} verrà revocato.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#374151',
        confirmButtonText: 'Sì, annulla',
        cancelButtonText: 'No, mantieni',
        background: '#1f2937',
        color: '#ffffff',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('teams.invitations.cancel', invitation.id), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Annullato!',
                        text: 'L\'invito è stato revocato.',
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
    <div class="pending-invites-section mt-8">
        <h3 class="text-lg font-semibold text-white mb-4">
            Inviti in sospeso
            <span
                v-if="invitations.length"
                class="ml-2 text-xs font-bold bg-amber-500/20 text-amber-400 px-2.5 py-0.5 rounded-full"
            >
                {{ invitations.length }}
            </span>
        </h3>

        <!-- Empty state -->
        <div
            v-if="!invitations.length"
            class="flex flex-col items-center justify-center py-10 bg-gray-800/30 border border-gray-700/50 rounded-xl"
        >
            <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center mb-3">
                <i class="fas fa-envelope-open text-gray-600 text-lg"></i>
            </div>
            <p class="text-sm text-gray-500">Nessun invito in sospeso</p>
        </div>

        <!-- Invitation rows -->
        <div v-else class="space-y-3">
            <div
                v-for="invitation in invitations"
                :key="invitation.id"
                class="flex items-center justify-between p-4 bg-gray-800/50 border border-gray-700 rounded-xl hover:border-gray-600 transition-all"
            >
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400 font-bold">
                        <i class="fas fa-envelope text-sm"></i>
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ invitation.email }}</p>
                        <p class="text-xs text-gray-500">
                            Inviato {{ timeAgo(invitation.created_at) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <span class="text-[10px] px-2 py-1 rounded bg-gray-900 text-amber-400 uppercase font-bold border border-gray-700">
                        {{ roleLabels[invitation.role] || invitation.role }}
                    </span>
                    <button
                        v-if="canManage"
                        @click="cancelInvitation(invitation)"
                        class="text-gray-500 hover:text-red-400 transition-colors p-2"
                        title="Annulla invito"
                    >
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
