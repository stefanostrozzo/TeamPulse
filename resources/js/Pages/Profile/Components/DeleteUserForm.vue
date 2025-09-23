<script setup>
import DangerButton from '@/Components/Items/DangerButton.vue';
import InputError from '@/Components/Items/InputError.vue';
import InputLabel from '@/Components/Items/InputLabel.vue';
import Modal from '@/Components/Items/Modal.vue';
import SecondaryButton from '@/Components/Items/SecondaryButton.vue';
import TextInput from '@/Components/Items/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                Elimina account
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Una volta eliminato il tuo account, tutte le sue risorse e dati
                verranno eliminati permanentemente. Prima di eliminare il tuo
                account, ti preghiamo di scaricare eventuali dati o
                informazioni che desideri conservare.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Elimina account</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="bg-gray-800 p-6 rounded-lg shadow">
                <h2
                    class="text-lg font-medium text-gray-900 dark:text-white"
                >
                    Sei sicuro di voler eliminare il tuo account?
                </h2>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Una volta eliminato il tuo account, tutte le sue risorse e
                    dati verranno eliminati permanentemente. Per confermare
                    l'eliminazione permanente del tuo account, per favore
                    inserisci la tua password.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Annulla
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Elimina account
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
