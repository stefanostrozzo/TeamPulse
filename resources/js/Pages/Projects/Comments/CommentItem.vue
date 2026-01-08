<script setup>
import { ref, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Swal from 'sweetalert2';

const props = defineProps({
    comment: Object
});

// Controls the display mode (view vs edit)
const isEditing = ref(false);

/**
 * Tiptap Editor for Inline Editing
 * Includes Image support to prevent image deletion on edit
 */
const editor = useEditor({
    content: props.comment.content,
    extensions: [
        StarterKit,
        Image.configure({
            allowBase64: true,
            HTMLAttributes: {
                class: 'rounded-xl border border-gray-700 max-w-full my-4 shadow-lg',
            },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'focus:outline-none min-h-[100px] text-gray-200 p-4 prose prose-invert max-w-none shadow-inner',
        },
    },
});

/**
 * Formats database timestamp to Italian localized string
 */
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

/**
 * Sends updated content to the server via Inertia.js
 * preserveState: true ensures the sidebar doesn't close
 */
const updateComment = () => {
    router.put(route('comments.update', props.comment.id), {
        content: editor.value.getHTML()
    }, {
        onSuccess: () => isEditing.value = false,
        preserveScroll: true,
        preserveState: true
    });
}

/**
 * Deletes the comment with a SweetAlert2 confirmation
 */
const deleteComment = () => {
    Swal.fire({
        title: 'Eliminare il commento?',
        text: "Non potrai annullare questa operazione",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#1f2937',
        confirmButtonText: 'Elimina',
        cancelButtonText: 'Annulla',
        background: '#111827',
        color: '#f3f4f6',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('comments.destroy', props.comment.id), {
                preserveScroll: true,
            });
        }
    });
};

onBeforeUnmount(() => {
    editor.value.destroy();
});
</script>

<template>
    <div class="bg-gray-800/40 p-5 rounded-2xl border border-gray-700/50 group mb-4 hover:border-gray-600 transition-all shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div class="flex flex-col">
                <span class="text-[#07b4f6] text-xs font-bold uppercase tracking-widest">
                    {{ comment.user?.name }}
                </span>
                <span class="text-gray-100 text-[11px] font-bold mt-1 flex items-center gap-1.5 bg-gray-700/50 px-2 py-0.5 rounded-md w-fit">
                    <i class="far fa-clock text-[#07b4f6]"></i>
                    {{ formatDate(comment.created_at) }}
                </span>
            </div>

            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                <button type="button" v-if="comment.can_edit && !isEditing" @click="isEditing = true" class="p-2 text-gray-400 hover:text-[#07b4f6] hover:bg-[#07b4f6]/10 rounded-lg transition-all">
                    <i class="fas fa-edit text-xs"></i>
                </button>
                <button type="button" v-if="comment.can_delete" @click="deleteComment" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-all">
                    <i class="fas fa-trash text-xs"></i>
                </button>
            </div>
        </div>

        <div v-if="isEditing" class="mt-3">
            <div class="bg-gray-900 rounded-xl overflow-hidden border border-[#07b4f6]/40">

                <div v-if="editor" class="flex items-center gap-1 p-2 border-b border-gray-800 bg-gray-950/50">
                    <button type="button" @click="editor.chain().focus().toggleBold().run()"
                            :class="{'bg-[#07b4f6]/20 text-[#07b4f6]': editor.isActive('bold'), 'text-gray-400': !editor.isActive('bold')}"
                            class="p-2 rounded-lg hover:bg-gray-800 transition-all">
                        <i class="fas fa-bold text-xs"></i>
                    </button>
                    <button type="button" @click="editor.chain().focus().toggleBulletList().run()"
                            :class="{'bg-[#07b4f6]/20 text-[#07b4f6]': editor.isActive('bulletList'), 'text-gray-400': !editor.isActive('bulletList')}"
                            class="p-2 rounded-lg hover:bg-gray-800 transition-all">
                        <i class="fas fa-list-ul text-xs"></i>
                    </button>
                </div>

                <EditorContent :editor="editor" />
            </div>
            <div class="flex justify-end gap-3 mt-3">
                <button type="button" @click="isEditing = false" class="text-xs text-gray-500 font-bold uppercase hover:text-white transition-colors">Annulla</button>
                <button type="button" @click="updateComment" class="bg-[#07b4f6] text-white text-[10px] font-bold px-4 py-2 rounded-xl uppercase tracking-widest shadow-lg shadow-[#07b4f6]/20">
                    Salva Modifiche
                </button>
            </div>
        </div>

        <div v-else class="text-gray-200 text-sm prose prose-invert max-w-none border-l-2 border-gray-700/50 pl-4 ml-1 leading-relaxed" v-html="comment.content"></div>
    </div>
</template>

<style scoped>
:deep(.prose ul) { list-style-type: disc; padding-left: 1.5rem; }
:deep(.prose img) { border-radius: 0.75rem; margin: 1rem 0; }
</style>
