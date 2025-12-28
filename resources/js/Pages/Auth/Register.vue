<script setup>
import InputError from '@/Components/Items/InputError.vue';
import InputLabel from '@/Components/Items/InputLabel.vue';
import PrimaryButton from '@/Components/Items/PrimaryButton.vue';
import TextInput from '@/Components/Items/TextInput.vue';
import ApplicationLogo from '@/Components/Items/ApplicationLogo.vue';
import {Head, Link, router, useForm} from '@inertiajs/vue3';
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
        onSuccess: () => {
            router.visit(route('home', { tab: 'dashboard' }));
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registrati" />

    <div class="flex min-h-screen items-center justify-center bg-gray-950 p-4 selection:bg-indigo-500 selection:text-white">
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px]"></div>
        </div>

        <div class="w-full max-w-lg">
            <div class="text-center mb-8">
                <Link href="/" class="inline-block transition-transform hover:scale-105">
                    <ApplicationLogo class="h-16 w-16 mx-auto" />
                </Link>
                <h1 class="mt-4 text-3xl font-bold text-white tracking-tight">Crea un Account</h1>
                <p class="text-gray-400 mt-2 text-sm">Registrati e inizia a gestire i tuoi progetti</p>
            </div>

            <div class="bg-gray-900/40 border border-gray-800 backdrop-blur-xl p-8 rounded-md shadow-2xl">
                <form @submit.prevent="submit" class="space-y-5">

                    <div>
                        <InputLabel for="name" value="Nome" class="text-gray-300 ml-1" />
                        <div class="relative mt-1">
                            <TextInput
                                id="name"
                                type="text"
                                class="block w-full bg-gray-800/50 border-gray-700 text-white focus:ring-indigo-500 rounded-md pl-4"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder=""
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Indirizzo Email" class="text-gray-300 ml-1" />
                        <div class="relative mt-1">
                            <TextInput
                                id="email"
                                type="email"
                                class="block w-full bg-gray-800/50 border-gray-700 text-white focus:ring-indigo-500 rounded-md pl-4"
                                v-model="form.email"
                                required
                                autocomplete="username"
                                placeholder=""
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <InputLabel for="password" value="Password" class="text-gray-300 ml-1" />
                            <div class="relative mt-1">
                                <TextInput
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full bg-gray-800/50 border-gray-700 text-white focus:ring-indigo-500 rounded-md pr-10"
                                    v-model="form.password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-indigo-400"
                                >
                                    <FontAwesomeIcon :icon="showPassword ? faEyeSlash : faEye" size="sm" />
                                </button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="relative">
                            <InputLabel for="password_confirmation" value="Conferma" class="text-gray-300 ml-1" />
                            <div class="relative mt-1">
                                <TextInput
                                    id="password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    class="block w-full bg-gray-800/50 border-gray-700 text-white focus:ring-indigo-500 rounded-md pr-10"
                                    v-model="form.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-indigo-400"
                                >
                                    <FontAwesomeIcon :icon="showPasswordConfirmation ? faEyeSlash : faEye" size="sm" />
                                </button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                    </div>

                    <div class="pt-4">
                        <PrimaryButton
                            class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-500 text-base font-semibold shadow-lg shadow-indigo-500/20 transition-all active:scale-95 rounded-md"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            Crea il mio account
                        </PrimaryButton>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-800 text-center">
                    <p class="text-gray-500 text-sm">
                        Hai già un account?
                        <Link :href="route('login')" class="text-indigo-400 font-semibold hover:text-indigo-300 underline-offset-4 hover:underline">
                            Accedi qui
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
