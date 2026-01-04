<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import InputError from '@/Components/Items/InputError.vue';

const props = defineProps({
    project: { type: Object, required: true },
    task: { type: Object, default: null }
});

const emit = defineEmits(['close', 'confirmDelete']);

const form = useForm({
    project_id: props.project.id,
    team_id: props.project.team_id,
    title: '',
    description: '',
    status: 'todo',
    priority: 'low',
    type: 'feature',
    assignee_id: null,
    start_date: '',
    due_date: '',
    progress: 0,
});

/**
 * Initialize or reset the form based on the selected task
 */
const fillForm = () => {
    if (props.task) {
        form.title = props.task.title ?? '';
        form.description = props.task.description ?? '';
        form.status = props.task.status ?? 'todo';
        form.priority = props.task.priority ?? 'low';
        form.type = props.task.type ?? 'feature';
        form.assignee_id = props.task.assignee_id ?? null;
        form.progress = props.task.progress ?? 0;
        form.start_date = props.task.start_date ? props.task.start_date.substring(0, 10) : '';
        form.due_date = props.task.due_date ? props.task.due_date.substring(0, 10) : '';
    } else {
        form.reset();
        form.project_id = props.project.id;
        form.team_id = props.project.team_id;
    }
};

onMounted(fillForm);
watch(() => props.task, fillForm);

const submit = () => {
    const options = {
        onSuccess: () => !props.task && emit('close'), // Close only on create, stay on edit
        preserveScroll: true,
    };

    if (props.task?.id) {
        form.put(route('tasks.update', props.task.id), options);
    } else {
        form.post(route('tasks.store'), options);
    }
};
</script>

<template>
    <div class="flex flex-col h-full bg-gray-900">
        <div class="sticky top-0 z-10 bg-gray-900/80 backdrop-blur-md p-6 border-b border-gray-800 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-tasks text-[#07b4f6]"></i>
                <span class="text-xs font-bold uppercase tracking-widest text-gray-500">Task Detail</span>
            </div>
            <div class="flex items-center space-x-2">
                <button v-if="task" @click="$emit('confirmDelete', task)" class="p-2 text-gray-500 hover:text-red-500 transition-colors">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button @click="$emit('close')" class="p-2 text-gray-500 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="flex-grow p-8 space-y-8 pb-32">
            <div>
                <textarea
                    v-model="form.title"
                    rows="1"
                    class="w-full bg-transparent border-none text-3xl font-bold text-white placeholder-gray-700 focus:ring-0 resize-none p-0"
                    placeholder="Task Title"
                ></textarea>
                <InputError :message="form.errors.title" />
            </div>

            <div class="space-y-4 text-sm">
                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-circle-notch mr-3 w-4"></i> Status</div>
                    <select v-model="form.status" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800">
                        <option value="todo">To Do</option>
                        <option value="in-progress">In Progress</option>
                        <option value="done">Done</option>
                        <option value="blocked">Blocked</option>
                    </select>
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-user-circle mr-3 w-4"></i> Assignee</div>
                    <select v-model="form.assignee_id" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800">
                        <option :value="null">Empty</option>
                        <option v-for="member in project.members" :key="member.id" :value="member.id">{{ member.name }}</option>
                    </select>
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="far fa-calendar mr-3 w-4"></i> Due Date</div>
                    <input type="date" v-model="form.due_date" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800">
                </div>

                <div class="grid grid-cols-3 items-center">
                    <div class="text-gray-500 flex items-center"><i class="fas fa-flag mr-3 w-4"></i> Priority</div>
                    <select v-model="form.priority" class="col-span-2 bg-transparent border-none text-white focus:ring-0 rounded-md hover:bg-gray-800 uppercase text-[10px] font-bold">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>

            <hr class="border-gray-800">

            <div>
                <div class="text-gray-500 text-xs font-bold uppercase mb-3">Description</div>
                <textarea
                    v-model="form.description"
                    rows="6"
                    class="w-full bg-transparent border-none text-gray-300 placeholder-gray-700 focus:ring-0 resize-none p-0 text-sm leading-relaxed"
                    placeholder="Add a detailed description..."
                ></textarea>
            </div>

            <div class="bg-gray-800/30 p-4 rounded-xl border border-gray-800">
                <div class="flex justify-between mb-2">
                    <span class="text-xs text-gray-500 font-bold uppercase">Progress</span>
                    <span class="text-[#07b4f6] font-bold text-xs">{{ form.progress }}%</span>
                </div>
                <input v-model="form.progress" type="range" step="10" class="w-full accent-[#07b4f6]">
            </div>

            <div v-if="task" class="pt-8 border-t border-gray-800">
                <div class="text-gray-500 text-xs font-bold uppercase mb-4">Comments</div>
                <div class="text-gray-600 italic text-sm text-center py-4 bg-gray-800/20 rounded-lg">
                    No comments yet.
                </div>
            </div>
        </form>

        <div class="absolute bottom-0 left-0 w-full p-6 bg-gray-900 border-t border-gray-800 flex justify-between items-center">
            <button @click="submit" :disabled="form.processing"
                    class="bg-[#07b4f6] hover:bg-[#06a3de] text-white px-6 py-2 rounded-lg text-sm font-bold shadow-lg transition-all active:scale-95 disabled:opacity-50">
                {{ form.processing ? 'Saving...' : (task ? 'Update' : 'Create Task') }}
            </button>
        </div>
    </div>
</template>
