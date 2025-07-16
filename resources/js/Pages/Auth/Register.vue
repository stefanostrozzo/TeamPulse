<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrati" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nome" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
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
                    autocomplete="new-password"
                />
                <!-- Icona mostra/nascondi password -->
                <button type="button" @click="showPassword = !showPassword" tabindex="-1"
                    class="absolute right-3 top-9 text-gray-400 hover:text-indigo-500 dark:text-gray-300 dark:hover:text-indigo-400 focus:outline-none">
                    <FontAwesomeIcon :icon="showPassword ? faEyeSlash : faEye" />
                </button>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 relative">
                <InputLabel
                    for="password_confirmation"
                    value="Conferma Password"
                />

                <TextInput
                    id="password_confirmation"
                    :type="showPasswordConfirmation ? 'text' : 'password'"
                    class="mt-1 block w-full pr-10"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <!-- Icona mostra/nascondi password conferma -->
                <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation" tabindex="-1"
                    class="absolute right-3 top-9 text-gray-400 hover:text-indigo-500 dark:text-gray-300 dark:hover:text-indigo-400 focus:outline-none">
                    <FontAwesomeIcon :icon="showPasswordConfirmation ? faEyeSlash : faEye" />
                </button>
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-base font-bold text-indigo-600 underline hover:text-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-indigo-400 dark:hover:text-white dark:shadow-lg dark:shadow-indigo-500/30"
                >
                    Hai già un account? Accedi
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Registrati
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
