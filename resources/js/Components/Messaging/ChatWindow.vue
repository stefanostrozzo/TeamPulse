<template>
    <div class="flex flex-col h-full bg-gray-900 absolute inset-0">
        <!-- Chat Header -->
        <div class="p-4 border-b border-gray-700 bg-gray-800/80 backdrop-blur-md flex items-center shrink-0 shadow-sm z-10">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br flex items-center justify-center mr-3 flex-shrink-0"
                :class="activeConversation?.is_group ? 'from-purple-600 to-indigo-600' : 'from-gray-600 to-gray-500'">
                <i v-if="activeConversation?.is_group" class="fas fa-users text-white text-sm"></i>
                <span v-else-if="displayName" class="text-white font-bold text-sm">{{ displayName.charAt(0).toUpperCase() }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-white text-base truncate">{{ displayName }}</h3>
                <div class="relative" v-if="activeConversation?.is_group">
                    <span
                        class="text-xs text-gray-400 cursor-pointer hover:text-white transition-colors"
                        @click="showParticipants = !showParticipants"
                    >
                        {{ activeConversation.participants.length }} membri
                    </span>
                    <div
                        v-if="showParticipants"
                        class="absolute top-full left-0 mt-1 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50 p-2 min-w-40"
                    >
                        <div
                            v-for="p in activeConversation.participants"
                            :key="p.id"
                            class="text-sm text-gray-200 py-1 px-2 hover:bg-gray-700 rounded"
                        >
                            {{ p.name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
            <div v-if="isLoadingMessages" class="flex justify-center p-4">
                <i class="fas fa-spinner fa-spin text-gray-500"></i>
            </div>

            <template v-else>
                <div v-if="store.hasMoreMessages" class="flex justify-center py-2">
                    <button
                        @click="store.fetchOlderMessages(activeConversationId)"
                        class="text-xs text-gray-400 hover:text-white bg-gray-700 hover:bg-gray-600 px-4 py-1.5 rounded-full transition-colors"
                    >
                        Carica messaggi precedenti
                    </button>
                </div>

                <div v-if="messages.length === 0" class="text-center text-gray-500 mt-10">
                    <p class="text-sm">Inizia la conversazione...</p>
                </div>

                <template v-for="msg in messages" :key="msg.id">
                    
                    <MessageBubble 
                        :message="msg" 
                        :is-mine="msg.sender_id === myId || msg.sender?.id === myId" 
                        :show-sender="activeConversation?.is_group" 
                    />
                </template>
            </template>
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-gray-800 border-t border-gray-700 shrink-0">
            <form @submit.prevent="submitMessage" class="flex items-end gap-2 relative">
                <div class="flex-1 bg-gray-900 border border-gray-700 rounded-xl overflow-hidden focus-within:border-[#07b4f6] focus-within:ring-1 focus-within:ring-[#07b4f6] transition-all">
                    <textarea 
                        v-model="newMessage" 
                        class="w-full bg-transparent border-none text-white text-sm focus:ring-0 resize-none p-3 max-h-32 min-h-[44px]"
                        rows="1"
                        placeholder="Scrivi un messaggio..."
                        @keydown.enter.prevent="handleEnter"
                    ></textarea>
                </div>
                <button 
                    type="submit" 
                    :disabled="!newMessage.trim() || isSending"
                    class="h-[44px] w-[44px] flex items-center justify-center rounded-xl bg-[#07b4f6] text-white hover:bg-[#069acc] disabled:opacity-50 disabled:cursor-not-allowed transition-colors shrink-0"
                >
                    <i class="fas fa-paper-plane text-sm" :class="{ 'fa-spinner fa-spin': isSending }"></i>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue';
import { useMessagingStore } from '@/stores/messagingStore';
import { storeToRefs } from 'pinia';
import MessageBubble from './MessageBubble.vue';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    conversationId: { type: Number, required: true }
});

const store = useMessagingStore();
const { activeConversation, messages, isLoadingMessages } = storeToRefs(store);
const toast = useToast();

const newMessage = ref('');
const isSending = ref(false);
const messagesContainer = ref(null);

const myId = computed(() => window.Laravel?.user?.id);
const showParticipants = ref(false);

// Load messages when conversation changes
watch(() => props.conversationId, async (newId) => {
    if (newId) {
        await store.fetchMessages(newId);
        scrollToBottom();
    }
}, { immediate: true });

// Scroll to bottom when new messages arrive
watch(() => messages.value.length, () => {
    scrollToBottom();
});

const displayName = computed(() => {
    if (!activeConversation.value) return '';
    if (activeConversation.value.is_group) return activeConversation.value.name;
    const other = activeConversation.value.participants.find(p => p.id !== myId.value);
    return other ? other.name : 'Unknown User';
});

const scrollToBottom = async () => {
    await nextTick();
    if (!messagesContainer.value) return;

    // Scroll to very bottom
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
};

const handleEnter = (e) => {
    if (!e.shiftKey) {
        submitMessage();
    }
};

const submitMessage = async () => {
    const text = newMessage.value.trim();
    if (!text || isSending.value) return;

    try {
        isSending.value = true;
        await store.sendMessage(props.conversationId, text);
        newMessage.value = '';
    } catch (e) {
        console.error('Send error:', e);
        toast.add({
            severity: 'error',
            summary: 'Errore',
            detail: 'Impossibile inviare il messaggio. Riprova.',
            life: 4000
        });
    } finally {
        isSending.value = false;
        await nextTick();
        scrollToBottom();
    }
};
</script>
