<script setup>
/**
 * Component to display individual team cards
 */
defineProps({
    team: Object,
    isCurrent: Boolean
});

defineEmits(['select', 'edit', 'open']);
</script>

<template>
    <div
        @click="$emit('open', team)"
        @contextmenu.prevent="$emit('edit', team)"
        class="relative group bg-gray-800 rounded-xl border border-gray-700 hover:border-[#07b4f6]/30 transition-all duration-300 p-6 cursor-pointer"
        :class="{'ring-2 ring-[#07b4f6]': isCurrent}"
    >
        <div v-if="isCurrent" class="absolute top-4 right-4 bg-[#07b4f6] text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">
            Attivo
        </div>

        <div class="mb-4">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-[#07b4f6] flex items-center justify-center text-white font-bold mb-3">
                {{ team.name.substring(0, 2).toUpperCase() }}
            </div>
            <h3 class="text-xl font-semibold text-white group-hover:text-[#07b4f6] transition-colors truncate">
                {{ team.name }}
            </h3>
            <p class="text-sm text-gray-400 mt-1">
                <i class="fas fa-user-friends mr-1"></i> {{ team.users_count || 0 }} membri
            </p>
        </div>

        <button
            @click.stop="$emit('select', team.id)"
            class="w-full mt-4 py-2 rounded-lg text-sm font-medium transition-all"
            :class="isCurrent
                ? 'bg-gray-700 text-gray-300 cursor-default'
                : 'bg-[#07b4f6]/10 text-[#07b4f6] hover:bg-[#07b4f6] hover:text-white'"
        >
            {{ isCurrent ? 'Già selezionato' : 'Seleziona Team' }}
        </button>
    </div>
</template>
