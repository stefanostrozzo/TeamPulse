<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    user: Object,
    mustVerifyEmail: Boolean,
    status: String
});

defineEmits(['toggle-sidebar', 'open-notifications']);
</script>

<template>
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden text-gray-900 dark:text-gray-100">
        <main class="flex-1 p-6 overflow-y-auto bg-gray-50 dark:bg-gray-950">
            <div class="max-w-9/10 mx-auto">

                <div>
                    <slot />
                </div>

            </div>
        </main>
    </div>
</template>
