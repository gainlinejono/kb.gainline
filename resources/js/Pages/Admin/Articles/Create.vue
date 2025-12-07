<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TiptapEditor from '@/Components/Editor/TiptapEditor.vue';

interface Group {
    id: number;
    name: string;
    parent: { name: string } | null;
}

interface Project {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    project: Project;
    groups: Group[];
}>();

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    group_id: '',
    status: 'draft',
});

const submit = () => {
    form.post(route('admin.projects.articles.store', props.project.slug));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Create Article - ${project.name}`" />

        <div class="mb-8">
            <Link :href="route('admin.projects.articles.index', project.slug)" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to Articles
            </Link>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Create Article</h1>
            <p class="text-sm text-gray-600">{{ project.name }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="space-y-6">
                            <div>
                                <InputLabel for="title" value="Title" />
                                <TextInput
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    placeholder="Article title"
                                />
                                <InputError :message="form.errors.title" class="mt-2" />
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
                                <InputLabel for="excerpt" value="Excerpt" />
                                <textarea
                                    id="excerpt"
                                    v-model="form.excerpt"
                                    rows="2"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Brief description of the article"
                                ></textarea>
                                <InputError :message="form.errors.excerpt" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow">
                        <InputLabel value="Content" class="mb-2" />
                        <TiptapEditor
                            v-model="form.content"
                            :project-id="project.id"
                            placeholder="Start writing your article..."
                        />
                        <InputError :message="form.errors.content" class="mt-2" />
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="text-sm font-medium text-gray-900">Publishing</h3>
                        <div class="mt-4 space-y-4">
                            <div>
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="group_id" value="Group" />
                                <select
                                    id="group_id"
                                    v-model="form.group_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">No group</option>
                                    <option v-for="group in groups" :key="group.id" :value="group.id">
                                        {{ group.parent ? `${group.parent.name} > ` : '' }}{{ group.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.group_id" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <PrimaryButton
                            :disabled="form.processing"
                            class="w-full justify-center"
                        >
                            {{ form.status === 'published' ? 'Publish Article' : 'Save as Draft' }}
                        </PrimaryButton>
                        <Link
                            :href="route('admin.projects.articles.index', project.slug)"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
