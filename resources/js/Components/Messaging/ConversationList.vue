<template>
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Search and Action Header -->
        <div class="p-4 border-b border-gray-700 shrink-0">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-white">Messaggi</h2>
                <button type="button" @click="$emit('new-group')" class="p-2 bg-gray-800 hover:bg-[#07b4f6]/20 text-[#07b4f6] text-sm rounded-lg transition" title="Nuovo gruppo">
                    <i class="fas fa-user-plus"></i>
                </button>
            </div>
            
            <ContactSearch @contact-selected="handleContactSelected" />
        </div>

        <!-- Conversation List -->
        <div class="flex-1 overflow-y-auto p-2 space-y-1">
            <div v-if="isLoadingConversations" class="text-center py-6 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p class="text-sm">Caricamento in corso...</p>
            </div>
            
            <div v-else-if="conversations.length === 0" class="text-center py-10 text-gray-500 px-4">
                <p class="text-sm">Nessuna conversazione trovata. Cerca un collega per iniziare.</p>
            </div>

            <ConversationItem 
                v-else
                v-for="convo in conversations" 
                :key="convo.id" 
                :conversation="convo"
                :active="activeConversationId === convo.id"
                @select="selectConversation" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useMessagingStore } from '@/stores/messagingStore';
import { storeToRefs } from 'pinia';
import ContactSearch from './ContactSearch.vue';
import ConversationItem from './ConversationItem.vue';

const emit = defineEmits(['new-group']);

const store = useMessagingStore();
const { conversations, isLoadingConversations, activeConversationId } = storeToRefs(store);

function selectConversation(id) {
    store.activeConversationId = id;
}

async function handleContactSelected(contact) {
    try {
        const convo = await store.createConversation([contact.id], null, false);
        store.activeConversationId = convo.id;
    } catch (e) {
        // Handled in store
    }
}
</script>
