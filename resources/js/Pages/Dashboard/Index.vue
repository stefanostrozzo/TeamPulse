<script setup>
import {computed} from "vue";
import {usePage} from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
</script>

<template>
    <div class="space-y-10 pb-10">
        <div class="border-b border-gray-800 pb-5">
            <h1 class="text-3xl font-bold text-white tracking-tight">Benvenuto, {{ user?.name }}</h1>
            <p class="text-gray-400 mt-2">Questa è la tua pagina principale</p>
        </div>
    </div>
</template>
