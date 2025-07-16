<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Home" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
                Benvenuto in TeamPulse
            </h2>
        </template>
        <div class="flex flex-col items-center justify-center w-full min-h-[60vh]">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 max-w-xl w-full text-center">
                <h1 class="text-3xl font-bold mb-4 text-gray-900 dark:text-white">Ciao e benvenuto 👋</h1>
                <p class="text-lg text-gray-700 dark:text-gray-300">
                    Questa è la tua homepage personale di TeamPulse.<br>
                    Da qui potrai accedere a tutte le funzionalità dell'applicazione.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 