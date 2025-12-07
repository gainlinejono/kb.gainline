<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { FolderIcon } from '@heroicons/vue/24/outline';

interface Project {
    id: number;
    name: string;
    slug: string;
    description: string;
    primary_color: string;
    logo_url: string | null;
}

defineProps<{
    projects: Project[];
}>();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Knowledgebase" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Knowledgebase</h1>
                    <p class="mt-2 text-gray-600">Select a project to view its documentation</p>
                </div>

                <div v-if="projects.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="project in projects"
                        :key="project.id"
                        :href="route('kb.show', project.slug)"
                        class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:border-gray-300 hover:shadow-md"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                v-if="project.logo_url"
                                class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg"
                            >
                                <img :src="project.logo_url" :alt="project.name" class="h-full w-full object-cover" />
                            </div>
                            <div
                                v-else
                                class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-lg text-white"
                                :style="{ backgroundColor: project.primary_color || '#3B82F6' }"
                            >
                                <FolderIcon class="h-6 w-6" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">
                                    {{ project.name }}
                                </h3>
                                <p v-if="project.description" class="mt-1 line-clamp-2 text-sm text-gray-500">
                                    {{ project.description }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="absolute bottom-0 left-0 h-1 w-full origin-left scale-x-0 transform transition-transform group-hover:scale-x-100"
                            :style="{ backgroundColor: project.primary_color || '#3B82F6' }"
                        ></div>
                    </Link>
                </div>

                <div v-else class="rounded-lg bg-white p-12 text-center shadow">
                    <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No projects available</h3>
                    <p class="mt-2 text-gray-500">
                        You don't have access to any projects yet. Please contact an administrator.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
