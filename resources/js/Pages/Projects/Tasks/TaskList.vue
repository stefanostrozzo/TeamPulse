<script setup>
defineProps({
    tasks: Array
});
</script>

<template>
    <div class="bg-gray-900/50 rounded-xl border border-gray-800 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="bg-gray-800/50 text-gray-400 text-xs uppercase tracking-widest">
                <th class="px-6 py-4 font-medium">Titolo</th>
                <th class="px-6 py-4 font-medium">Stato</th>
                <th class="px-6 py-4 font-medium">Priorità</th>
                <th class="px-6 py-4 font-medium">Scadenza</th>
                <th class="px-6 py-4 font-medium text-right">Progresso</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
            <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-800/30 transition-colors group">
                <td class="px-6 py-4">
                    <div class="text-white font-medium group-hover:text-[#07b4f6]">{{ task.title }}</div>
                    <div class="text-xs text-gray-500">{{ task.type }}</div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs text-gray-300">{{ task.status }}</span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs text-gray-300">{{ task.priority }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-400">
                    {{ task.due_date || '-' }}
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="text-xs font-bold text-[#07b4f6]">{{ task.progress }}%</span>
                </td>
            </tr>
            <tr v-if="tasks.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                    Nessuna task trovata per questo progetto.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
