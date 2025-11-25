<script setup>
defineProps({
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            completed: 0,
            overdue: 0
        })
    }
});

const statCards = [
    {
        key: 'total',
        label: 'Totale Progetti',
        icon: 'fas fa-project-diagram',
        color: 'from-blue-500 to-cyan-400',
        bgColor: 'bg-gradient-to-br from-blue-500/10 to-cyan-400/10',
        borderColor: 'border-blue-500/20'
    },
    {
        key: 'active',
        label: 'In Corso',
        icon: 'fas fa-play-circle',
        color: 'from-green-500 to-emerald-400',
        bgColor: 'bg-gradient-to-br from-green-500/10 to-emerald-400/10',
        borderColor: 'border-green-500/20'
    },
    {
        key: 'completed',
        label: 'Completati',
        icon: 'fas fa-check-circle',
        color: 'from-purple-500 to-fuchsia-400',
        bgColor: 'bg-gradient-to-br from-purple-500/10 to-fuchsia-400/10',
        borderColor: 'border-purple-500/20'
    },
    {
        key: 'overdue',
        label: 'In Ritardo',
        icon: 'fas fa-exclamation-circle',
        color: 'from-red-500 to-orange-400',
        bgColor: 'bg-gradient-to-br from-red-500/10 to-orange-400/10',
        borderColor: 'border-red-500/20'
    }
];
</script>

<template>
    <div class="project-stats grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
            v-for="card in statCards" 
            :key="card.key"
            class="stat-card group relative bg-gray-800 rounded-xl border border-gray-700 p-6 hover:border-gray-600 transition-all duration-300 hover:transform hover:scale-105"
            :class="card.bgColor"
        >
            <!-- Border hover effect -->
            <div class="absolute inset-0 rounded-xl border-2 border-transparent group-hover:border-white/5 transition-colors"></div>
            
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-3xl font-bold text-white mb-1">
                        {{ stats[card.key] || 0 }}
                    </div>
                    <div class="text-sm font-medium text-gray-300">
                        {{ card.label }}
                    </div>
                </div>
                
                <div class="relative">
                    <!-- Icon background glow -->
                    <div class="absolute inset-0 rounded-full opacity-20 blur-sm" :class="card.color.replace('from-', 'bg-gradient-to-r from-')"></div>
                    
                    <!-- Main icon -->
                    <div class="relative w-12 h-12 rounded-xl flex items-center justify-center" :class="card.bgColor">
                        <i class="text-xl" :class="[card.icon, card.color.replace('from-', 'text-').split(' ')[0]]"></i>
                    </div>
                </div>
            </div>

            <!-- Progress bar (solo per completati) -->
            <div v-if="card.key === 'completed' && stats.total > 0" class="mt-4">
                <div class="flex justify-between text-xs text-gray-400 mb-1">
                    <span>Percentuale completamento</span>
                    <span>{{ Math.round((stats.completed / stats.total) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                    <div 
                        class="h-2 rounded-full transition-all duration-500"
                        :class="card.color.replace('from-', 'bg-gradient-to-r from-')"
                        :style="{ width: Math.round((stats.completed / stats.total) * 100) + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Trend indicator (solo per in ritardo) -->
            <div v-if="card.key === 'overdue' && stats.overdue > 0" class="mt-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                    <i class="fas fa-arrow-up mr-1"></i>
                    Attenzione
                </span>
            </div>

            <!-- Success rate (solo per attivi) -->
            <div v-if="card.key === 'active' && stats.total > 0" class="mt-2 text-xs text-gray-400">
                {{ Math.round((stats.active / stats.total) * 100) }}% del totale
            </div>
        </div>
    </div>
</template>

<style scoped>
.stat-card {
    backdrop-filter: blur(10px);
}

.stat-card:hover {
    box-shadow: 0 10px 30px -10px rgba(7, 180, 246, 0.1);
}
</style>