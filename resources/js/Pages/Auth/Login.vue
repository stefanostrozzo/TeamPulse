<script setup>
import Checkbox from '@/Components/Items/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/Items/InputError.vue';
import InputLabel from '@/Components/Items/InputLabel.vue';
import PrimaryButton from '@/Components/Items/PrimaryButton.vue';
import TextInput from '@/Components/Items/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Accedi" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 relative">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="mt-1 block w-full pr-10"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <!-- Icona mostra/nascondi password -->
                <button type="button" @click="showPassword = !showPassword" tabindex="-1"
                    class="absolute right-3 top-9 text-gray-400 hover:text-indigo-500 dark:text-gray-300 dark:hover:text-indigo-400 focus:outline-none">
                    <FontAwesomeIcon :icon="showPassword ? faEyeSlash : faEye" />
                </button>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-white-600"
                        >Ricordami</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Accedi
                </PrimaryButton>
            </div>
            <div class="mt-4 flex items-center justify-center">
                <Link
                    :href="route('register')"
                    class="rounded-md text-base font-bold text-indigo-600 underline hover:text-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-indigo-400 dark:hover:text-white dark:shadow-lg dark:shadow-indigo-500/30"
                >
                    Non hai un account? Registrati
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
