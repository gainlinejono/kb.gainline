<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilIcon, TrashIcon, ChevronRightIcon, FolderIcon } from '@heroicons/vue/24/outline';

interface Group {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    order: number;
    is_visible: boolean;
    articles_count: number;
    children: Group[];
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

const deleteGroup = (group: Group) => {
    if (confirm(`Are you sure you want to delete "${group.name}"?`)) {
        router.delete(route('admin.projects.groups.destroy', [props.project.slug, group.id]));
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Groups - ${project.name}`" />

        <div class="mb-6">
            <Link :href="route('admin.projects.edit', project.slug)" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to {{ project.name }}
            </Link>
        </div>

        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Groups</h1>
                <p class="mt-1 text-sm text-gray-600">Organize articles into groups and subgroups</p>
            </div>
            <Link
                :href="route('admin.projects.groups.create', project.slug)"
                class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:mt-0"
            >
                <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" />
                New Group
            </Link>
        </div>

        <div class="mt-6 rounded-lg bg-white shadow">
            <div v-if="groups.length > 0" class="divide-y divide-gray-200">
                <template v-for="group in groups" :key="group.id">
                    <!-- Parent Group -->
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <FolderIcon class="h-5 w-5 text-gray-400" />
                                <div>
                                    <p class="font-medium text-gray-900">{{ group.name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ group.articles_count }} articles
                                        <span v-if="group.children.length > 0">
                                            &middot; {{ group.children.length }} subgroups
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    :class="[
                                        group.is_visible ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                                        'inline-flex rounded-full px-2 text-xs font-semibold'
                                    ]"
                                >
                                    {{ group.is_visible ? 'Visible' : 'Hidden' }}
                                </span>
                                <div class="flex gap-2">
                                    <Link
                                        :href="route('admin.projects.groups.edit', [project.slug, group.id])"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        <PencilIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        @click="deleteGroup(group)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Child Groups -->
                    <template v-for="child in group.children" :key="child.id">
                        <div class="bg-gray-50 p-4 pl-12 hover:bg-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <ChevronRightIcon class="h-4 w-4 text-gray-400" />
                                    <FolderIcon class="h-5 w-5 text-gray-400" />
                                    <div>
                                        <p class="font-medium text-gray-900">{{ child.name }}</p>
                                        <p class="text-sm text-gray-500">{{ child.articles_count }} articles</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span
                                        :class="[
                                            child.is_visible ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                                            'inline-flex rounded-full px-2 text-xs font-semibold'
                                        ]"
                                    >
                                        {{ child.is_visible ? 'Visible' : 'Hidden' }}
                                    </span>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('admin.projects.groups.edit', [project.slug, child.id])"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            @click="deleteGroup(child)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nested Child Groups -->
                        <template v-for="subchild in child.children" :key="subchild.id">
                            <div class="bg-gray-100 p-4 pl-20 hover:bg-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <ChevronRightIcon class="h-4 w-4 text-gray-400" />
                                        <FolderIcon class="h-5 w-5 text-gray-400" />
                                        <div>
                                            <p class="font-medium text-gray-900">{{ subchild.name }}</p>
                                            <p class="text-sm text-gray-500">{{ subchild.articles_count }} articles</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('admin.projects.groups.edit', [project.slug, subchild.id])"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            <PencilIcon class="h-5 w-5" />
                                        </Link>
                                        <button
                                            @click="deleteGroup(subchild)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </template>
                </template>
            </div>
            <div v-else class="p-12 text-center">
                <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">No groups yet</h3>
                <p class="mt-2 text-gray-500">
                    Groups help organize your articles into categories.
                </p>
                <Link
                    :href="route('admin.projects.groups.create', project.slug)"
                    class="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-500"
                >
                    <PlusIcon class="mr-1 h-5 w-5" />
                    Create your first group
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>
