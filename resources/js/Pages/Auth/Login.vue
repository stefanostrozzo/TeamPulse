<script setup>
import Checkbox from '@/Components/Items/Checkbox.vue';
import InputError from '@/Components/Items/InputError.vue';
import InputLabel from '@/Components/Items/InputLabel.vue';
import PrimaryButton from '@/Components/Items/PrimaryButton.vue';
import TextInput from '@/Components/Items/TextInput.vue';
import ApplicationLogo from '@/Components/Items/ApplicationLogo.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token') || null;

const form = useForm({
    email: '',
    password: '',
    remember: false,
    token: token,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onSuccess: () => {
            if(!token) {
                router.visit(route('home', {tab: 'dashboard'}));
            }
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Accedi" />

    <div class="flex min-h-screen items-center justify-center bg-gray-950 p-4 selection:bg-indigo-500 selection:text-white">
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-600/20 rounded-full blur-[120px]"></div>
        </div>

        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <Link href="/" class="inline-block transition-transform hover:scale-105">
                    <ApplicationLogo class="h-20 w-20 mx-auto" />
                </Link>
                <h1 class="mt-4 text-3xl font-bold text-white tracking-tight">Bentornato su Team<span class="text-[#07b4f6]">Pulse</span></h1>
                <p class="text-gray-400 mt-2 text-sm italic">Accedi per gestire i tuoi progetti</p>
            </div>

            <div class="bg-gray-900/40 border border-gray-800 backdrop-blur-xl p-8 rounded-3xl shadow-2xl">
                <div v-if="status" class="mb-4 text-sm font-medium text-green-400 text-center bg-green-400/10 p-3 rounded-xl border border-green-400/20">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-300 ml-1" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full bg-gray-800/50 border-gray-700 text-white focus:ring-indigo-500 rounded-md"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder=""
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="relative">
                        <div class="relative mt-1">
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="block w-full bg-gray-800/50 border-gray-700 text-white focus:ring-indigo-500 rounded-md pr-12"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder=""
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-indigo-400 transition-colors"
                            >
                                <FontAwesomeIcon :icon="showPassword ? faEyeSlash : faEye" />
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-gray-700 bg-gray-800 text-indigo-500" />
                        <span class="ms-2 text-sm text-gray-400">Resta collegato</span>
                    </div>

                    <div class="pt-2">
                        <PrimaryButton
                            class="w-full justify-center py-3 bg-indigo-600 hover:bg-indigo-500 text-base font-semibold shadow-lg shadow-indigo-500/20 transition-all active:scale-95"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            Accedi
                        </PrimaryButton>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-800 text-center">
                    <p class="text-gray-500 text-sm">
                        Non hai ancora un account?
                        <Link :href="route('register')" class="text-indigo-400 font-semibold hover:text-indigo-300 underline-offset-4 hover:underline">
                            Registrati ora
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
