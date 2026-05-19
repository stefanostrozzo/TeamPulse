<template>
    <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" v-show="isOpen">
        <div class="absolute inset-0 overflow-hidden">
            <!-- Background overlay -->
            <transition
                enter-active-class="ease-in-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in-out duration-300"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="isOpen" class="absolute inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="$emit('close')" aria-hidden="true"></div>
            </transition>

            <div class="fixed inset-y-0 right-0 max-w-full flex">
                <!-- Slide-over panel -->
                <transition
                    enter-active-class="transform transition ease-in-out duration-300 sm:duration-500"
                    enter-from-class="translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-active-class="transform transition ease-in-out duration-300 sm:duration-500"
                    leave-from-class="translate-x-0"
                    leave-to-class="translate-x-full"
                >
                    <div v-if="isOpen" class="w-screen max-w-sm">
                        <div class="h-full flex flex-col bg-gray-900 border-l border-gray-700 shadow-xl relative">
                            <!-- Header -->
                            <div class="px-6 py-6 bg-gray-800 border-b border-gray-700 flex items-center justify-between shrink-0">
                                <h2 class="text-xl font-bold text-white flex items-center" id="slide-over-title">
                                    <i class="fas fa-bell mr-3 text-[#07b4f6]"></i>
                                    Messaggi non letti
                                </h2>
                                <button type="button" class="rounded-md text-gray-400 hover:text-white focus:outline-none transition group" @click="$emit('close')">
                                    <span class="sr-only">Chiudi</span>
                                    <i class="fas fa-times text-xl group-hover:scale-110 transition-transform"></i>
                                </button>
                            </div>

                            <!-- List -->
                            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                                <div v-if="unreadConversations.length === 0" class="h-full flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-check-circle text-5xl mb-4 text-green-500/50"></i>
                                    <p class="text-lg">Nessun nuovo messaggio</p>
                                    <p class="text-sm mt-2 text-center">Tutti i messaggi sono stati letti.</p>
                                </div>

                                <div v-for="convo in unreadConversations" :key="convo.id" 
                                     @click="openConversation(convo.id)"
                                     class="p-4 rounded-xl bg-gray-800 border border-gray-700 hover:border-[#07b4f6] cursor-pointer transition-all group relative overflow-hidden">
                                     
                                    <div class="absolute inset-y-0 left-0 w-1 bg-[#07b4f6]"></div>

                                    <div class="flex items-start ml-2">
                                        <!-- Avatar -->
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br flex items-center justify-center flex-shrink-0 mt-1"
                                            :class="convo.is_group ? 'from-purple-600 to-indigo-600' : 'from-gray-600 to-gray-500'">
                                            <i v-if="convo.is_group" class="fas fa-users text-white text-xs"></i>
                                            <span v-else class="text-white font-bold text-sm">{{ getDisplayName(convo).charAt(0).toUpperCase() }}</span>
                                        </div>
                                        
                                        <div class="ml-3 flex-1">
                                            <div class="flex justify-between items-baseline mb-1">
                                                <h4 class="text-sm font-bold text-white truncate max-w-[150px]">{{ getDisplayName(convo) }}</h4>
                                                <span class="text-xs text-[#07b4f6] font-medium">{{ formatTime(convo.last_message_at) }}</span>
                                            </div>
                                            <p class="text-sm text-gray-300 line-clamp-2 leading-relaxed">
                                                <span v-if="convo.is_group && convo.latest_message" class="text-gray-400 font-semibold mr-1">
                                                    {{ convo.latest_message.sender?.name.split(' ')[0] }}:
                                                </span>
                                                {{ convo.latest_message ? convo.latest_message.body : '...' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer actions -->
                            <div v-if="unreadConversations.length > 0" class="p-4 border-t border-gray-700 bg-gray-800 shrink-0">
                                <button type="button" @click="markAllAsRead" class="w-full py-2.5 px-4 bg-gray-800 hover:bg-gray-700 text-white rounded-lg transition border border-gray-600 font-medium text-sm flex items-center justify-center">
                                    <i class="fas fa-check-double mr-2 text-gray-400"></i>
                                    Segna tutti come letti
                                </button>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue';
import { useMessagingStore } from '@/stores/messagingStore';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['close', 'navigate']);

const store = useMessagingStore();

const myId = computed(() => window.Laravel?.user?.id);

/**
 * Refresh conversations from the server whenever the drawer is opened.
 * This is a fallback in case any real-time Echo events were missed during
 * a WebSocket disconnection or before the subscription was established.
 */
watch(() => props.isOpen, (open) => {
    if (open) {
        store.refreshConversations();
    }
});

const unreadConversations = computed(() => {
    if (!myId.value) return [];
    return store.conversations.filter(c => {
        const me = c.participants.find(p => p.id === myId.value);
        if (!me || !c.latest_message) return false;
        if (!me.pivot.last_read_at) return true;
        return new Date(c.latest_message.created_at) > new Date(me.pivot.last_read_at);
    });
});

function getDisplayName(convo) {
    if (convo.is_group) return convo.name;
    const other = convo.participants.find(p => p.id !== myId.value);
    return other ? other.name : 'Utente';
}

function formatTime(timestamp) {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    if (isNaN(date.getTime())) return '';
    const today = new Date();
    if (date.toDateString() === today.toDateString()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
}

function openConversation(id) {
    emit('close');
    emit('navigate', id);
}

async function markAllAsRead() {
    await Promise.all(unreadConversations.value.map(c => store.markAsRead(c.id)));
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
