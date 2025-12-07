<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    FolderIcon,
    DocumentTextIcon,
    UsersIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';

interface Stats {
    projects: number;
    articles: number;
    published_articles: number;
    users: number;
    total_views: number;
}

interface Article {
    id: number;
    title: string;
    status: string;
    created_at: string;
    author: { name: string };
    project: { name: string; slug: string };
}

interface Project {
    id: number;
    name: string;
    slug: string;
    articles_count: number;
    created_at: string;
}

defineProps<{
    stats: Stats;
    recentArticles: Article[];
    recentProjects: Project[];
}>();

const statCards = [
    { name: 'Total Projects', key: 'projects', icon: FolderIcon, color: 'bg-blue-500' },
    { name: 'Total Articles', key: 'articles', icon: DocumentTextIcon, color: 'bg-green-500' },
    { name: 'Published Articles', key: 'published_articles', icon: DocumentTextIcon, color: 'bg-purple-500' },
    { name: 'Total Users', key: 'users', icon: UsersIcon, color: 'bg-orange-500' },
    { name: 'Total Views', key: 'total_views', icon: EyeIcon, color: 'bg-pink-500' },
];
</script>

<template>
    <AdminLayout>
        <Head title="Admin Dashboard" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Overview of your knowledgebase</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
            <div
                v-for="stat in statCards"
                :key="stat.key"
                class="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:px-6"
            >
                <dt>
                    <div :class="[stat.color, 'absolute rounded-md p-3']">
                        <component :is="stat.icon" class="h-6 w-6 text-white" />
                    </div>
                    <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ stat.name }}</p>
                </dt>
                <dd class="ml-16 flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ stats[stat.key as keyof Stats]?.toLocaleString() }}
                    </p>
                </dd>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Recent Projects -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Projects</h3>
                        <Link :href="route('admin.projects.index')" class="text-sm text-indigo-600 hover:text-indigo-500">
                            View all
                        </Link>
                    </div>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="project in recentProjects" :key="project.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <Link
                                :href="route('admin.projects.show', project.slug)"
                                class="truncate text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >
                                {{ project.name }}
                            </Link>
                            <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                {{ project.articles_count }} articles
                            </span>
                        </div>
                    </li>
                    <li v-if="recentProjects.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                        No projects yet.
                        <Link :href="route('admin.projects.create')" class="text-indigo-600 hover:text-indigo-500">
                            Create one
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Recent Articles -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Articles</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li v-for="article in recentArticles" :key="article.id" class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ article.title }}</p>
                                <p class="text-xs text-gray-500">
                                    by {{ article.author.name }} in {{ article.project.name }}
                                </p>
                            </div>
                            <span
                                :class="[
                                    article.status === 'published' ? 'bg-green-100 text-green-800' :
                                    article.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-gray-100 text-gray-800',
                                    'ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium'
                                ]"
                            >
                                {{ article.status }}
                            </span>
                        </div>
                    </li>
                    <li v-if="recentArticles.length === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                        No articles yet.
                    </li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>
