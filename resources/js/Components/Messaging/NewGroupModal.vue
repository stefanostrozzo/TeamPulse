<template>
    <transition name="whatsapp" appear>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" @click.self="handleClose">
            <div class="bg-gray-800 rounded-xl shadow-2xl border border-gray-700 w-full max-w-md overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center bg-gray-800 shrink-0">
                    <h3 class="text-lg font-bold text-white">Nuovo Gruppo</h3>
                    <button type="button" @click="handleClose" class="text-gray-400 hover:text-white transition">
                        <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 flex-1 overflow-y-auto">
                <form @submit.prevent="submitGroup" class="space-y-6">
                    
                    <!-- Nome Gruppo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nome Gruppo</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required
                            placeholder="Es: Developers & Designers"
                            class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-[#07b4f6] focus:border-[#07b4f6]"
                        >
                    </div>

                    <!-- Selezione Partecipanti -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Aggiungi Membri</label>
                        <div class="mb-3">
                            <ContactSearch @contact-selected="addParticipant" />
                        </div>
                        
                        <!-- Selected Participants Chips -->
                        <div v-if="selectedParticipants.length > 0" class="flex flex-wrap gap-2 mt-3 p-3 bg-gray-900 rounded-lg border border-gray-800">
                            <span v-for="user in selectedParticipants" :key="user.id" 
                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-[#07b4f6]/20 text-[#07b4f6] border border-[#07b4f6]/30">
                                {{ user.name }}
                                <button type="button" @click="removeParticipant(user.id)" class="ml-2 hover:text-white transition group">
                                    <i class="fas fa-times group-hover:scale-110"></i>
                                </button>
                            </span>
                        </div>
                        <p v-else class="text-xs text-gray-500 italic mt-2">Nessun membro selezionato.</p>
                    </div>

                    <div v-if="error" class="text-red-400 text-sm p-3 bg-red-900/20 rounded border border-red-500/30">
                        {{ error }}
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-700 bg-gray-800 flex justify-end gap-3 shrink-0">
                <button type="button" @click="handleClose" class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                    Annulla
                </button>
                <button type="button" @click="submitGroup" 
                    :disabled="isSubmitting || !form.name || selectedParticipants.length === 0"
                    class="px-4 py-2 text-sm font-medium text-white bg-[#07b4f6] rounded-lg hover:bg-[#069acc] disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center">
                    <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
                    Crea gruppo
                </button>
            </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { useMessagingStore } from '@/stores/messagingStore';
import ContactSearch from './ContactSearch.vue';
import Swal from 'sweetalert2';

const emit = defineEmits(['close']);
const store = useMessagingStore();

const form = reactive({ name: '' });
const selectedParticipants = ref([]);
const isSubmitting = ref(false);
const error = ref('');

function handleClose() {
    if (form.name.trim() !== '' || selectedParticipants.value.length > 0) {
        Swal.fire({
            title: 'Sei sicuro?',
            text: 'Se esci, perderai le informazioni inserite fino ad ora, continuare?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sì, esci',
            cancelButtonText: 'Annulla'
        }).then((result) => {
            if (result.isConfirmed) {
                emit('close');
            }
        });
    } else {
        emit('close');
    }
}

function handleEsc(e) {
    if (e.key === 'Escape') {
        handleClose();
    }
}

onMounted(() => window.addEventListener('keydown', handleEsc));
onUnmounted(() => window.removeEventListener('keydown', handleEsc));

function addParticipant(user) {
    if (!selectedParticipants.value.find(p => p.id === user.id)) {
        selectedParticipants.value.push(user);
    }
}

function removeParticipant(userId) {
    selectedParticipants.value = selectedParticipants.value.filter(p => p.id !== userId);
}

async function submitGroup() {
    if (!form.name || selectedParticipants.value.length === 0) {
        error.value = "Inserisci un nome per il gruppo e seleziona almeno un membro.";
        return;
    }

    error.value = '';
    isSubmitting.value = true;

    try {
        const pIds = selectedParticipants.value.map(p => p.id);
        const convo = await store.createConversation(pIds, form.name, true);
        store.activeConversationId = convo.id;
        emit('close');
    } catch (e) {
        console.error(e);
        error.value = "Errore durante la creazione del gruppo.";
    } finally {
        isSubmitting.value = false;
    }
}
</script>
<style scoped>
.whatsapp-enter-active,
.whatsapp-leave-active {
  transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.whatsapp-enter-from,
.whatsapp-leave-to {
  opacity: 0;
  transform: scale(0.9) translateY(20px);
}

</style>
