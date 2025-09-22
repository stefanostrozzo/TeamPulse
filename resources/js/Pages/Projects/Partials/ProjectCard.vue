<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    project: {
        type: Object,
        required: true
    }
});

const statusColors = {
    planning: 'bg-blue-500',
    active: 'bg-green-500',
    paused: 'bg-yellow-500',
    completed: 'bg-gray-500',
    cancelled: 'bg-red-500'
};

const priorityColors = {
    low: 'text-green-400',
    medium: 'text-yellow-400',
    high: 'text-orange-400',
    urgent: 'text-red-400'
};
</script>

<template>
    <div class="project-card group bg-gray-800 rounded-xl border border-gray-700 hover:border-[#07b4f6]/30 transition-all duration-300 hover:shadow-lg hover:shadow-[#07b4f6]/5">
        <Link :href="route('progetti.show', project.id)" class="block p-5">
            <!-- Header -->
            <div class="flex justify-between items-start mb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full" :class="statusColors[project.status]"></div>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ project.status }}</span>
                </div>
                <div class="flex items-center space-x-1">
                    <i class="fas fa-star text-xs" :class="priorityColors[project.priority]"></i>
                    <span class="text-xs text-gray-400">{{ project.priority }}</span>
                </div>
            </div>

            <!-- Titolo e Descrizione -->
            <h3 class="text-lg font-semibold text-white mb-2 group-hover:text-[#07b4f6] transition-colors">
                {{ project.name }}
            </h3>
            <p class="text-sm text-gray-400 mb-4 line-clamp-2">{{ project.description }}</p>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>Progresso</span>
                    <span>{{ project.progress }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div 
                        class="bg-gradient-to-r from-[#07b4f6] to-cyan-400 h-2 rounded-full transition-all duration-500"
                        :style="{ width: project.progress + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Info Bottom -->
            <div class="flex justify-between items-center text-sm">
                <div class="text-gray-400">
                    <i class="far fa-calendar mr-1"></i>
                    <span v-if="project.end_date">
                        {{ new Date(project.end_date).toLocaleDateString('it-IT') }}
                    </span>
                    <span v-else>Nessuna scadenza</span>
                </div>
                <div class="flex -space-x-2">
                    <div 
                        v-for="member in project.members.slice(0, 3)"
                        :key="member.id"
                        class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-[#07b4f6] flex items-center justify-center text-xs font-semibold text-white border-2 border-gray-800"
                        :title="member.name"
                    >
                        {{ member.name.substring(0, 1) }}
                    </div>
                    <div 
                        v-if="project.members.length > 3"
                        class="w-6 h-6 rounded-full bg-gray-600 flex items-center justify-center text-xs text-gray-300 border-2 border-gray-800"
                    >
                        +{{ project.members.length - 3 }}
                    </div>
                </div>
            </div>
        </Link>
    </div>
</template>