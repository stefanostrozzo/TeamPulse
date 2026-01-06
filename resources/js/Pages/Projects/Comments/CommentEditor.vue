<script setup>
import { ref } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    processing: Boolean
});

const emit = defineEmits(['save', 'cancel']);
const content = ref('');

const handleSave = () => {
    if (!content.value || content.value === '<p><br></p>') return;
    emit('save', content.value);
    content.value = '';
};
</script>

<template>
    <div class="mb-8">
        <div class="bg-gray-800 rounded-2xl border border-gray-700 overflow-hidden shadow-inner">
            <QuillEditor
                v-model:content="content"
                contentType="html"
                theme="snow"
                placeholder="Scrivi qui il tuo commento..."
            />
        </div>
        <div class="mt-3 flex justify-end gap-3">
            <button type="button" @click="emit('cancel')" class="text-xs font-bold text-gray-500 hover:text-white px-2">
                Annulla
            </button>
            <button
                type="button"
                @click="handleSave"
                :disabled="processing"
                class="bg-[#07b4f6] text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-[#06a3de] transition-all disabled:opacity-50"
            >
                {{ processing ? 'Invio...' : 'Invia' }}
            </button>
        </div>
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

/* Hover sui pulsanti della toolbar */
.ql-snow.ql-toolbar button:hover .ql-stroke,
.ql-snow .ql-toolbar button:hover .ql-stroke {
    stroke: #07b4f6 !important; /* Il tuo azzurro */
}

/* FIX per il tema chiaro sugli hover/tooltip */
.ql-snow .ql-tooltip {
    background-color: #1f2937 !important; /* gray-800 */
    color: #f3f4f6 !important; /* gray-100 */
    border: 1px solid #374151 !important; /* gray-700 */
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
}

.ql-snow .ql-tooltip input[type=text] {
    background: #111827 !important; /* gray-900 */
    border: 1px solid #4b5563 !important;
    color: white !important;
}

/* Colore di sfondo della toolbar */
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

/* Questo corregge i pulsanti che diventano bianchi al click/hover */
:deep(.ql-snow.ql-toolbar button:hover),
:deep(.ql-snow.ql-toolbar button.ql-active) {
    background-color: #1f2937;
}
</style>
