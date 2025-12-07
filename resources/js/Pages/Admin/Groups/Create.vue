<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

interface Group {
    id: number;
    name: string;
}

interface Project {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    project: Project;
    parentGroups: Group[];
}>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    icon: '',
    parent_id: '',
    is_visible: true,
});

const submit = () => {
    form.post(route('admin.projects.groups.store', props.project.slug));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Create Group - ${project.name}`" />

        <div class="mb-8">
            <Link :href="route('admin.projects.groups.index', project.slug)" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to Groups
            </Link>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Create Group</h1>
        </div>

        <form @submit.prevent="submit" class="max-w-2xl">
            <div class="space-y-6 rounded-lg bg-white p-6 shadow">
                <div>
                    <InputLabel for="name" value="Group Name" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="slug" value="Slug" />
                    <TextInput
                        id="slug"
                        v-model="form.slug"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Leave empty to auto-generate"
                    />
                    <InputError :message="form.errors.slug" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="parent_id" value="Parent Group (Optional)" />
                    <select
                        id="parent_id"
                        v-model="form.parent_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option value="">No parent (root level)</option>
                        <option v-for="group in parentGroups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.parent_id" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="icon" value="Icon (Optional)" />
                    <TextInput
                        id="icon"
                        v-model="form.icon"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="e.g., book, folder, code"
                    />
                    <p class="mt-1 text-xs text-gray-500">Enter an icon name or SVG code</p>
                    <InputError :message="form.errors.icon" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input
                        id="is_visible"
                        v-model="form.is_visible"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                    />
                    <label for="is_visible" class="ml-2 text-sm text-gray-900">Visible in navigation</label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <Link :href="route('admin.projects.groups.index', project.slug)" class="text-sm text-gray-600 hover:text-gray-900">
                    Cancel
                </Link>
                <PrimaryButton :disabled="form.processing">
                    Create Group
                </PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
