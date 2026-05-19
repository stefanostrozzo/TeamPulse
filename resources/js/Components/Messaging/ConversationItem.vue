<template>
    <button 
        type="button" 
        class="w-full flex items-center p-3 rounded-xl transition-colors duration-200 text-left"
        :class="active ? 'bg-[#07b4f6]/10 border border-[#07b4f6]/30' : 'hover:bg-gray-800 border border-transparent'"
        @click="$emit('select', conversation.id)"
    >
        <!-- Avatar -->
        <div class="relative w-12 h-12 rounded-full bg-gradient-to-br flex items-center justify-center flex-shrink-0"
            :class="conversation.is_group ? 'from-purple-600 to-indigo-600' : 'from-gray-600 to-gray-500'">
            <i v-if="conversation.is_group" class="fas fa-users text-white"></i>
            <span v-else class="text-white font-bold text-lg">{{ displayName.charAt(0).toUpperCase() }}</span>
            
            <!-- Unread Indicator -->
            <div v-if="isUnread" class="absolute top-0 right-0 w-3 h-3 bg-[#07b4f6] rounded-full ring-2 ring-gray-900 border border-white"></div>
        </div>

        <!-- Meta -->
        <div class="ml-4 flex-1 min-w-0">
            <div class="flex justify-between items-baseline mb-0.5">
                <h4 class="text-sm font-semibold text-white truncate" :class="{ 'font-bold': isUnread }">
                    {{ displayName }}
                </h4>
                <span class="text-[10px] text-gray-500 flex-shrink-0 ml-2">
                    {{ formattedTime }}
                </span>
            </div>
            <p class="text-sm text-gray-400 truncate" :class="{ 'text-gray-300 font-medium': isUnread }">
                <span v-if="conversation.latest_message && conversation.is_group" class="text-gray-500 font-medium mr-1">
                    {{ conversation.latest_message.sender?.name.split(' ')[0] }}:
                </span>
                {{ snippet }}
            </p>
        </div>
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    conversation: { type: Object, required: true },
    active: { type: Boolean, default: false }
});

defineEmits(['select']);

const myId = computed(() => window.Laravel?.user?.id);

const isUnread = computed(() => {
    if (!myId.value || !props.conversation.latest_message) return false;
    const me = props.conversation.participants.find(p => p.id === myId.value);
    if (!me) return false;
    if (!me.pivot.last_read_at) return true;
    return new Date(props.conversation.latest_message.created_at) > new Date(me.pivot.last_read_at);
});

const displayName = computed(() => {
    if (props.conversation.is_group) return props.conversation.name;
    const other = props.conversation.participants.find(p => p.id !== myId.value);
    return other ? other.name : 'Unknown User';
});

const snippet = computed(() => {
    if (!props.conversation.latest_message) return 'Nessun messaggio';
    return props.conversation.latest_message.body;
});

const formattedTime = computed(() => {
    if (!props.conversation.last_message_at) return '';
    const date = new Date(props.conversation.last_message_at);
    if (isNaN(date.getTime())) return '';
    
    // If today, show time, else show short date
    const today = new Date();
    if (date.toDateString() === today.toDateString()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
});
</script>
