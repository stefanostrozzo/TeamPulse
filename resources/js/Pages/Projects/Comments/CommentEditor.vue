<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import { onBeforeUnmount } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    processing: Boolean
});

const emit = defineEmits(['save', 'cancel']);

const editor = useEditor({
    content: '',
    extensions: [
        StarterKit,
        Image.configure({
            allowBase64: true, // Allows pasting images directly
            HTMLAttributes: {
                class: 'rounded-xl border border-gray-700 max-w-full my-4 shadow-lg',
            },
        }),
    ],
    editorProps: {
        attributes: {
            class: 'focus:outline-none min-h-[120px] text-gray-200 p-4 prose prose-invert max-w-none shadow-inner',
        },
    },
});

const handleSave = () => {
    const html = editor.value.getHTML();
    if (!html || html === '<p></p>') return;

    emit('save', html);
    editor.value.commands.clearContent();
};

onBeforeUnmount(() => {
    editor.value.destroy();
});
</script>

<template>
    <div class="mb-8 group">
        <div class="bg-gray-900/50 rounded-2xl border border-gray-800 focus-within:border-[#07b4f6]/50 transition-all overflow-hidden shadow-2xl">

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

        <div class="mt-3 flex justify-end gap-3 opacity-0 group-focus-within:opacity-100 transition-opacity duration-300">
            <button type="button" @click="emit('cancel')" class="text-[11px] font-bold text-gray-500 hover:text-white uppercase tracking-widest">
                Annulla
            </button>
            <button type="button" @click="handleSave" :disabled="processing"
                    class="bg-[#07b4f6] hover:bg-[#06a3de] text-white text-[11px] font-bold px-6 py-2 rounded-xl shadow-lg shadow-[#07b4f6]/20 transition-all active:scale-95 disabled:opacity-50 uppercase tracking-widest">
                Aggiungi
            </button>
        </div>
    </div>
</template>

<style scoped>
.tiptap:focus { outline: none; }
:deep(.prose ul) { list-style-type: disc; padding-left: 1.5rem; }
</style>
