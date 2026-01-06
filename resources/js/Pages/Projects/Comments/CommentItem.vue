<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { QuillEditor } from '@vueup/vue-quill';
import Swal from 'sweetalert2';

const props = defineProps({
    comment: Object
});

const isEditing = ref(false);
const editedContent = ref(props.comment.content);

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const updateComment = () => {
    router.put(route('comments.update', props.comment.id), {
        content: editedContent.value
    }, {
        onSuccess: () => isEditing.value = false,
        preserveScroll: true,
        preserveState: true
    });
};

const deleteComment = () => {
    Swal.fire({
        title: 'Eliminare il commento?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#07b4f6',
        cancelButtonColor: '#1f2937',
        confirmButtonText: 'Elimina',
        cancelButtonText: 'Annulla',
        background: '#111827',
        color: '#f3f4f6',
        allowOutsideClick: false,
        stopKeydownPropagation: false,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('comments.destroy', props.comment.id), {
                preserveScroll: true,
                preserveState: true,
            });
        }
    });
};
</script>

<template>
    <div class="bg-gray-800/40 p-4 rounded-2xl border border-gray-700/50 group mb-4">
        <div class="flex justify-between items-start mb-2">
            <div class="flex flex-col">
                <span class="text-[#07b4f6] text-[11px] font-bold uppercase tracking-wider">
                    {{ comment.user?.name }}
                </span>
                <span class="text-gray-200 text-xs font-semibold mt-1 flex items-center gap-1">
                    <i class="far fa-clock text-[#07b4f6]"></i>
                    {{ formatDate(comment.created_at) }}
                </span>
            </div>

            <div class="flex gap-2" @click.stop>
                <button v-if="comment.can_edit && !isEditing" @click="isEditing = true" class="text-gray-400 hover:text-yellow-500 transition-colors">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <button v-if="comment.can_delete" @click="deleteComment" class="text-gray-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-trash text-sm"></i>
                </button>
            </div>
        </div>

        <div v-if="isEditing" class="mt-3">
            <div class="bg-gray-900 rounded-xl overflow-hidden border border-gray-700">
                <QuillEditor v-model:content="editedContent" contentType="html" theme="snow" />
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button @click="isEditing = false" class="text-xs text-gray-400 uppercase p-2">Annulla</button>
                <button @click="updateComment" class="bg-[#07b4f6] text-white text-xs font-bold px-4 py-1.5 rounded-lg">Salva</button>
            </div>
        </div>

        <div v-else class="text-gray-200 text-sm ql-editor p-0 mt-2 leading-relaxed" v-html="comment.content"></div>
    </div>
</template>

<style>
/* Colore delle icone e dei testi dei pulsanti */
.ql-snow .ql-stroke {
    stroke: #d1d5db !important; /* gray-300 */
}
.ql-snow .ql-fill {
    fill: #d1d5db !important;
}
.ql-snow .ql-picker {
    color: #d1d5db !important;
}

.ql-snow.ql-toolbar button:hover .ql-stroke,
.ql-snow .ql-toolbar button:hover .ql-stroke {
    stroke: #07b4f6 !important; /* Il tuo azzurro */
}

.ql-snow .ql-tooltip {
    background-color: #1f2937 !important;
    color: #f3f4f6 !important;
    border: 1px solid #374151 !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
}

.ql-snow .ql-tooltip input[type=text] {
    background: #111827 !important; /* gray-900 */
    border: 1px solid #4b5563 !important;
    color: white !important;
}

.ql-toolbar.ql-snow {
    border-color: #374151 !important;
    background-color: #1f2937 !important;
}
</style>

<style scoped>
:deep(.ql-toolbar.ql-snow) {
    border-color: #374151;
    background-color: #111827;
}

:deep(.ql-snow .ql-stroke) {
    stroke: #9ca3af;
}

:deep(.ql-snow .ql-picker) {
    color: #9ca3af;
}

:deep(.ql-snow.ql-toolbar button:hover .ql-stroke) {
    stroke: #07b4f6;
}

:deep(.ql-snow.ql-toolbar button:hover),
:deep(.ql-snow.ql-toolbar button.ql-active) {
    background-color: #1f2937;
}
</style>
