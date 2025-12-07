<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash-es';

interface Project {
    id: number;
    name: string;
    slug: string;
    description: string;
    is_active: boolean;
    articles_count: number;
    members_count: number;
    created_at: string;
}

interface Pagination<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

const props = defineProps<{
    projects: Pagination<Project>;
    filters: { search?: string };
}>();

const search = ref(props.filters.search || '');

const debouncedSearch = debounce((value: string) => {
    router.get(route('admin.projects.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch(search, (value) => {
    debouncedSearch(value);
});

const deleteProject = (project: Project) => {
    if (confirm(`Are you sure you want to delete "${project.name}"?`)) {
        router.delete(route('admin.projects.destroy', project.slug));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Projects" />

        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
                <p class="mt-1 text-sm text-gray-600">Manage your knowledgebase projects</p>
            </div>
            <Link
                :href="route('admin.projects.create')"
                class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:mt-0"
            >
                <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" />
                New Project
            </Link>
        </div>

        <!-- Search -->
        <div class="mt-6">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                </div>
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search projects..."
                    class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                />
            </div>
        </div>

        <!-- Projects Table -->
        <div class="mt-6 overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Articles
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Members
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="project in projects.data" :key="project.id">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div>
                                <Link
                                    :href="route('admin.projects.show', project.slug)"
                                    class="font-medium text-gray-900 hover:text-indigo-600"
                                >
                                    {{ project.name }}
                                </Link>
                                <p class="text-sm text-gray-500">{{ project.slug }}</p>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ project.articles_count }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ project.members_count }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span
                                :class="[
                                    project.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                                    'inline-flex rounded-full px-2 text-xs font-semibold leading-5'
                                ]"
                            >
                                {{ project.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="route('admin.projects.edit', project.slug)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <button
                                    @click="deleteProject(project)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="projects.data.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                            No projects found.
                            <Link :href="route('admin.projects.create')" class="text-indigo-600 hover:text-indigo-500">
                                Create your first project
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav v-if="projects.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ (projects.current_page - 1) * projects.per_page + 1 }}</span>
                            to
                            <span class="font-medium">{{ Math.min(projects.current_page * projects.per_page, projects.total) }}</span>
                            of
                            <span class="font-medium">{{ projects.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                            <Link
                                v-for="link in projects.links"
                                :key="link.label"
                                :href="link.url || ''"
                                :class="[
                                    link.active ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                    !link.url ? 'cursor-not-allowed opacity-50' : '',
                                    'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                                ]"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </nav>
        </div>
    </AdminLayout>
</template>
