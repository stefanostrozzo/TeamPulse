<script setup>
import InputError from '@/Components/Items/InputError.vue';
import InputLabel from '@/Components/Items/InputLabel.vue';
import PrimaryButton from '@/Components/Items/PrimaryButton.vue';
import TextInput from '@/Components/Items/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showNewPassword = ref(false);
const showNewPasswordConfirmation = ref(false); // Corretto il nome

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-white">
                Aggiorna password
            </h2>

            <p class="mt-1 text-sm text-gray-300">
                Assicurati che la tua password sia lunga e casuale per
                mantenere la tua sicurezza.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div class="relative">
                <InputLabel for="current_password" value="Password corrente" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="form.current_password"
                    class="mt-1 block w-full pr-10"
                    autocomplete="current-password"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    tabindex="-1"
                    class="absolute right-3 top-8 text-gray-400 hover:text-indigo-500 focus:outline-none"
                >
                    <FontAwesomeIcon :icon="showPassword ? faEyeSlash : faEye" />
                </button>
                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <div class="relative">
                <InputLabel for="password" value="Nuova password" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    :type="showNewPassword ? 'text' : 'password'"
                    v-model="form.password"
                    class="mt-1 block w-full pr-10"
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    @click="showNewPassword = !showNewPassword"
                    tabindex="-1"
                    class="absolute right-3 top-8 text-gray-400 hover:text-indigo-500 focus:outline-none"
                >
                    <FontAwesomeIcon :icon="showNewPassword ? faEyeSlash : faEye" />
                </button>
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div class="relative">
                <InputLabel
                    for="password_confirmation"
                    value="Conferma password"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="showNewPasswordConfirmation ? 'text' : 'password'"
                    class="mt-1 block w-full pr-10"
                    autocomplete="new-password"
                />

                <button
                    type="button"
                    @click="showNewPasswordConfirmation = !showNewPasswordConfirmation"
                    tabindex="-1"
                    class="absolute right-3 top-8 text-gray-400 hover:text-indigo-500 focus:outline-none"
                >
                    <FontAwesomeIcon :icon="showNewPasswordConfirmation ? faEyeSlash : faEye" />
                </button>
                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Salva</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-400"
                    >
                        Salvato.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
