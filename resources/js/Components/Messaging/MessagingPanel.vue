<template>
    <div class="flex h-[calc(100vh-8rem)] bg-gray-900 overflow-hidden rounded-xl border border-gray-700 w-full">
        
        <!-- Left Sidebar: Conversations List -->
        <div class="w-1/3 min-w-[300px] border-r border-gray-700 flex flex-col bg-gray-800">
            <ConversationList @new-group="showNewGroupModal = true" />
        </div>

        <!-- Right Panel: Active Chat -->
        <div class="w-2/3 flex flex-col bg-gray-900 relative">
            <ChatWindow v-if="activeConversationId" :conversation-id="activeConversationId" />
            <div v-else class="flex-1 flex flex-col items-center justify-center text-gray-500">
                <i class="fas fa-comments text-6xl mb-4 opacity-50"></i>
                <p class="text-lg">Seleziona una conversazione per iniziare</p>
            </div>
        </div>

        <NewGroupModal v-if="showNewGroupModal" @close="showNewGroupModal = false" />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useMessagingStore } from '@/stores/messagingStore';
import { storeToRefs } from 'pinia';

import ConversationList from './ConversationList.vue';
import ChatWindow from './ChatWindow.vue';
import NewGroupModal from './NewGroupModal.vue';

const store = useMessagingStore();
const { activeConversationId } = storeToRefs(store);

const showNewGroupModal = ref(false);

/**
 * Bug 8 fix: Removed the duplicate store.setAuthUser() + store.fetchConversations() calls
 * that were previously here. The store is already initialised (idempotently) by Home.vue
 * via store.initialize(), so calling it again from MessagingPanel caused a redundant
 * HTTP request, a brief loading flash, and potential race conditions with ongoing operations.
 */
</script>

