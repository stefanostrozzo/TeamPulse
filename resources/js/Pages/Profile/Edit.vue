<script setup>
import UpdateProfileInformationForm from '@/Pages/Profile/Components/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Components/UpdatePasswordForm.vue';
import DeleteUserForm from '@/Pages/Profile/Components/DeleteUserForm.vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';

</script>

<template>
 <div class="bg-gray-800 p-6 rounded-lg shadow">
    <UpdateProfileInformationForm 
        :must-verify-email="mustVerifyEmail" 
        :status="status" 
        class="max-w-xl"
    />
</div>

<div class="bg-gray-800 p-6 rounded-lg shadow">
    <UpdatePasswordForm class="max-w-xl" />
</div>

<div class="bg-gray-800 p-6 rounded-lg shadow">
    <DeleteUserForm class="max-w-xl" />
</div>
</template>