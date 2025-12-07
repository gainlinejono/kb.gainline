<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    primary_color: '#3B82F6',
    basecamp_project_id: '',
    is_active: true,
    logo: null as File | null,
});

const submit = () => {
    form.post(route('admin.projects.store'), {
        forceFormData: true,
    });
};

const handleLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.logo = target.files[0];
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Project" />

        <div class="mb-8">
            <Link :href="route('admin.projects.index')" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to Projects
            </Link>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Create Project</h1>
        </div>

        <form @submit.prevent="submit" class="max-w-2xl">
            <div class="space-y-6 rounded-lg bg-white p-6 shadow">
                <div>
                    <InputLabel for="name" value="Project Name" />
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
                    <InputLabel for="slug" value="Slug (URL-friendly name)" />
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
                    <InputLabel for="primary_color" value="Primary Color" />
                    <div class="mt-1 flex items-center gap-3">
                        <input
                            id="primary_color"
                            v-model="form.primary_color"
                            type="color"
                            class="h-10 w-20 cursor-pointer rounded border border-gray-300"
                        />
                        <TextInput
                            v-model="form.primary_color"
                            type="text"
                            class="w-32"
                            placeholder="#3B82F6"
                        />
                    </div>
                    <InputError :message="form.errors.primary_color" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="logo" value="Logo" />
                    <input
                        id="logo"
                        type="file"
                        accept="image/*"
                        @change="handleLogoChange"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                    />
                    <InputError :message="form.errors.logo" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="basecamp_project_id" value="Basecamp Project ID (Optional)" />
                    <TextInput
                        id="basecamp_project_id"
                        v-model="form.basecamp_project_id"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="For future Basecamp integration"
                    />
                    <InputError :message="form.errors.basecamp_project_id" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <input
                        id="is_active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                    />
                    <label for="is_active" class="ml-2 text-sm text-gray-900">Active</label>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-4">
                <Link :href="route('admin.projects.index')" class="text-sm text-gray-600 hover:text-gray-900">
                    Cancel
                </Link>
                <PrimaryButton :disabled="form.processing">
                    Create Project
                </PrimaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
