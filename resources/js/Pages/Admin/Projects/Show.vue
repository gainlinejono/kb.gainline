<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FolderIcon, DocumentTextIcon, UsersIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

interface Article {
    id: number;
    title: string;
    status: string;
    created_at: string;
}

interface Group {
    id: number;
    name: string;
    children: Group[];
}

interface Member {
    id: number;
    name: string;
    email: string;
    pivot: { role: string };
}

interface Project {
    id: number;
    name: string;
    slug: string;
    description: string;
    primary_color: string;
    is_active: boolean;
    logo_url: string | null;
    members: Member[];
    root_groups: Group[];
    articles: Article[];
}

defineProps<{
    project: Project;
}>();
</script>

<template>
    <AdminLayout>
        <Head :title="project.name" />

        <div class="mb-6">
            <Link :href="route('admin.projects.index')" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to Projects
            </Link>
        </div>

        <div class="mb-8 flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div
                    v-if="project.logo_url"
                    class="h-16 w-16 overflow-hidden rounded-lg"
                >
                    <img :src="project.logo_url" :alt="project.name" class="h-full w-full object-cover" />
                </div>
                <div
                    v-else
                    class="flex h-16 w-16 items-center justify-center rounded-lg text-2xl font-bold text-white"
                    :style="{ backgroundColor: project.primary_color }"
                >
                    {{ project.name.charAt(0) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ project.name }}</h1>
                    <p v-if="project.description" class="mt-1 text-gray-600">{{ project.description }}</p>
                    <span
                        :class="[
                            project.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800',
                            'mt-2 inline-flex rounded-full px-2 text-xs font-semibold'
                        ]"
                    >
                        {{ project.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2">
                <Link
                    :href="route('kb.show', project.slug)"
                    target="_blank"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                >
                    View KB
                </Link>
                <Link
                    :href="route('admin.projects.edit', project.slug)"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500"
                >
                    <Cog6ToothIcon class="-ml-1 mr-2 h-5 w-5" />
                    Settings
                </Link>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Link
                :href="route('admin.projects.articles.index', project.slug)"
                class="group flex items-center gap-4 rounded-lg bg-white p-6 shadow hover:shadow-md"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 group-hover:bg-blue-200">
                    <DocumentTextIcon class="h-6 w-6 text-blue-600" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ project.articles?.length || 0 }}</p>
                    <p class="text-sm text-gray-500">Articles</p>
                </div>
            </Link>

            <Link
                :href="route('admin.projects.groups.index', project.slug)"
                class="group flex items-center gap-4 rounded-lg bg-white p-6 shadow hover:shadow-md"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 group-hover:bg-purple-200">
                    <FolderIcon class="h-6 w-6 text-purple-600" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ project.root_groups?.length || 0 }}</p>
                    <p class="text-sm text-gray-500">Groups</p>
                </div>
            </Link>

            <Link
                :href="route('admin.projects.edit', project.slug)"
                class="group flex items-center gap-4 rounded-lg bg-white p-6 shadow hover:shadow-md"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 group-hover:bg-green-200">
                    <UsersIcon class="h-6 w-6 text-green-600" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ project.members?.length || 0 }}</p>
                    <p class="text-sm text-gray-500">Members</p>
                </div>
            </Link>
        </div>

        <!-- Recent Articles -->
        <div class="mt-8">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Recent Articles</h2>
                <Link
                    :href="route('admin.projects.articles.create', project.slug)"
                    class="text-sm text-indigo-600 hover:text-indigo-500"
                >
                    New Article
                </Link>
            </div>
            <div class="mt-4 rounded-lg bg-white shadow">
                <ul v-if="project.articles && project.articles.length > 0" class="divide-y divide-gray-200">
                    <li v-for="article in project.articles" :key="article.id" class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ article.title }}</p>
                                <p class="text-sm text-gray-500">{{ new Date(article.created_at).toLocaleDateString() }}</p>
                            </div>
                            <span
                                :class="[
                                    article.status === 'published' ? 'bg-green-100 text-green-800' :
                                    article.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-gray-100 text-gray-800',
                                    'inline-flex rounded-full px-2 text-xs font-semibold'
                                ]"
                            >
                                {{ article.status }}
                            </span>
                        </div>
                    </li>
                </ul>
                <div v-else class="p-6 text-center text-sm text-gray-500">
                    No articles yet.
                    <Link :href="route('admin.projects.articles.create', project.slug)" class="text-indigo-600 hover:text-indigo-500">
                        Create your first article
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
