<template>
    <div class="topbar bg-gray-800 border-b border-gray-700 px-6 pt-4 pb-3 flex items-center justify-between">
        <div class="search-container relative w-full max-w-xl">
            <span class="search-icon absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 104.243 11.964l4.271 4.272a.75.75 0 101.061-1.06l-4.272-4.272A6.75 6.75 0 0010.5 3.75zM5.25 10.5a5.25 5.25 0 1110.5 0 5.25 5.25 0 01-10.5 0z" clip-rule="evenodd" />
                </svg>
            </span>
            <input
                type="text"
                class="search-input w-full bg-gray-700 rounded-xl pl-11 pr-4 py-2 text-sm text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#07b4f6]"
                :placeholder="placeholder"
            />
        </div>

        <div class="topbar-actions flex items-center gap-6">
            <button class="command-palette-btn flex items-center gap-2 bg-gray-700 text-gray-100 rounded-xl px-3 py-2 text-sm hover:bg-[#07b4f6] transition" @click="$emit('command')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.121L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m-6 0h6m-6 0a3 3 0 01-3-3V8.25a3 3 0 013-3h6a3 3 0 013 3v6a3 3 0 01-3 3m-6 0h6" />
                </svg>
                <span class="hidden md:inline">Command Palette</span>
            </button>

            <button class="action-btn w-10 h-10 rounded-xl bg-gray-700 text-gray-100 hover:bg-[#07b4f6] transition relative" @click="$emit('toggle-notifications')">
                <span v-if="notifications > 0" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] flex items-center justify-center">
                    {{ notifications }}
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M5.25 7.5A6.75 6.75 0 0112 0.75 6.75 6.75 0 0118.75 7.5v3.818c0 .512.203 1.003.564 1.364l1.061 1.061A1.5 1.5 0 0119.318 15H4.682a1.5 1.5 0 01-1.057-2.557l1.061-1.061c.361-.361.564-.852.564-1.364V7.5z" />
                    <path d="M8.25 18a3.75 3.75 0 007.5 0h-7.5z" />
                </svg>
            </button>

            <div class="flex items-center gap-3 border-l border-gray-700 pl-6 ml-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-semibold text-white leading-tight">
                        {{ $page.props.auth.user.name }}
                    </p>
                    <p v-if="activeTeamName" class="text-[10px] text-[#07b4f6] uppercase font-bold tracking-widest flex items-center justify-end gap-1">
                        <i class="fas fa-users text-[8px]"></i>
                        {{ activeTeamName }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed } from 'vue';
    import { usePage } from '@inertiajs/vue3';

    const page = usePage();

    const props = defineProps({
        notifications: { type: [String, Number], default: 0 },
        placeholder: { type: String, default: 'Cerca progetti, task o membri...' },
    });

    defineEmits(['toggle-notifications', 'command']);

    // Logic to find the active team name from the user's team list
    const activeTeamName = computed(() => {
        const user = page.props.auth.user;
        if (!user.current_team_id || !user.teams) return null;

        const team = user.teams.find(t => t.id === user.current_team_id);
        return team ? team.name : null;
    });
</script>
