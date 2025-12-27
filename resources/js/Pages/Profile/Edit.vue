<script setup>
import UpdateProfileInformationForm from '@/Pages/Profile/Components/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Components/UpdatePasswordForm.vue';
import DeleteUserForm from '@/Pages/Profile/Components/DeleteUserForm.vue';

</script>

<template>
    <div class="space-y-10 pb-10">
        <div class="border-b border-gray-800 pb-5">
            <h1 class="text-3xl font-bold text-white tracking-tight">Impostazioni Profilo</h1>
            <p class="text-gray-400 mt-2">Gestisci le tue preferenze e la sicurezza dell'account.</p>
        </div>

        <div class="grid grid-cols-1 gap-8">
            <div class="bg-gray-900/50 border border-gray-800 p-8 rounded-2xl shadow-sm backdrop-blur-sm">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-2xl"
                />
            </div>

            <div class="bg-gray-900/50 border border-gray-800 p-8 rounded-2xl shadow-sm backdrop-blur-sm">
                <UpdatePasswordForm class="max-w-2xl" />
            </div>

            <div class="bg-red-900/5 border border-red-900/20 p-8 rounded-2xl shadow-sm">
                <DeleteUserForm class="max-w-2xl" />
            </div>
        </div>
    </div>
</template>
