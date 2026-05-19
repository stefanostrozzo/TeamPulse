<template>
    <div class="flex w-full mb-4" :class="isMine ? 'justify-end' : 'justify-start'">
        <!-- Avatar for others -->
        <div v-if="!isMine && showSender" class="flex-shrink-0 mr-3">
            <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-xs font-bold text-white">
                {{ senderInitial }}
            </div>
        </div>

        <div class="max-w-[75%] flex flex-col" :class="isMine ? 'items-end' : 'items-start'">
            <!-- Sender Name -->
            <span v-if="!isMine && showSender" class="text-xs text-gray-400 mb-1 ml-1">{{ message.sender?.name }}</span>
            
            <!-- Bubble -->
            <div class="px-4 py-2.5 rounded-2xl break-words"
                :class="isMine 
                    ? 'bg-[#07b4f6] text-white rounded-tr-sm' 
                    : 'bg-gray-800 border border-gray-700 text-gray-100 rounded-tl-sm'">
                <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ message.body }}</p>
            </div>
            
            <!-- Timestamp -->
            <span class="text-[10px] text-gray-500 mt-1 mx-1">{{ formattedTime }}</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: { type: Object, required: true },
    isMine: { type: Boolean, required: true },
    showSender: { type: Boolean, default: false }
});

const formattedTime = computed(() => {
    const date = new Date(props.message.created_at);
    if (isNaN(date.getTime())) return '';
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

const senderInitial = computed(() => {
    return props.message.sender?.name?.charAt(0).toUpperCase() || '?';
});
</script>
