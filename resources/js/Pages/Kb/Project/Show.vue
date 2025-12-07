<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import KbLayout from '@/Layouts/KbLayout.vue';
import {
    DocumentTextIcon,
    BookOpenIcon,
    ClockIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';

interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    views: number;
    reading_time: number;
    published_at: string;
}

interface Group {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string | null;
    children: Group[];
    articles: Article[];
}

interface Project {
    id: number;
    name: string;
    slug: string;
    description: string;
    primary_color: string;
    logo_url: string | null;
}

defineProps<{
    project: Project;
    navigation: any[];
    featuredArticles: Article[];
    recentArticles: Article[];
}>();
</script>

<template>
    <KbLayout :project="project" :navigation="navigation">
        <Head :title="project.name" />

        <!-- Hero Section -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900">
                {{ project.name }} Documentation
            </h1>
            <p v-if="project.description" class="mt-4 text-lg text-gray-600">
                {{ project.description }}
            </p>
        </div>

        <!-- Quick Start Cards -->
        <div v-if="navigation && navigation.length > 0" class="mb-12">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">Getting Started</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="group in navigation.slice(0, 6)"
                    :key="group.id"
                    :href="route('kb.group', [project.slug, group.path])"
                    class="group rounded-xl border border-gray-200 bg-white p-6 transition-all hover:border-gray-300 hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg"
                            :style="{ backgroundColor: project.primary_color + '20' }"
                        >
                            <BookOpenIcon
                                class="h-5 w-5"
                                :style="{ color: project.primary_color }"
                            />
                        </div>
                        <h3 class="font-semibold text-gray-900 group-hover:text-[var(--primary-color)]">
                            {{ group.name }}
                        </h3>
                    </div>
                    <p v-if="group.articles.length > 0" class="mt-3 text-sm text-gray-500">
                        {{ group.articles.length }} article{{ group.articles.length !== 1 ? 's' : '' }}
                    </p>
                    <div class="mt-4 flex items-center text-sm font-medium" :style="{ color: project.primary_color }">
                        Explore
                        <ArrowRightIcon class="ml-1 h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </div>
                </Link>
            </div>
        </div>

        <!-- Featured Articles -->
        <div v-if="featuredArticles.length > 0" class="mb-12">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">Popular Articles</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <Link
                    v-for="article in featuredArticles"
                    :key="article.id"
                    :href="route('kb.article', [project.slug, article.slug])"
                    class="group flex items-start gap-4 rounded-lg border border-gray-200 bg-white p-4 transition-all hover:border-gray-300 hover:shadow-sm"
                >
                    <div
                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100"
                    >
                        <DocumentTextIcon class="h-5 w-5 text-gray-600" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-medium text-gray-900 group-hover:text-[var(--primary-color)]">
                            {{ article.title }}
                        </h3>
                        <p v-if="article.excerpt" class="mt-1 line-clamp-2 text-sm text-gray-500">
                            {{ article.excerpt }}
                        </p>
                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-400">
                            <span class="flex items-center gap-1">
                                <ClockIcon class="h-3.5 w-3.5" />
                                {{ article.reading_time || 1 }} min read
                            </span>
                            <span>{{ article.views.toLocaleString() }} views</span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Recent Articles -->
        <div v-if="recentArticles.length > 0">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">Recently Updated</h2>
            <div class="space-y-3">
                <Link
                    v-for="article in recentArticles"
                    :key="article.id"
                    :href="route('kb.article', [project.slug, article.slug])"
                    class="group flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 transition-all hover:border-gray-300 hover:shadow-sm"
                >
                    <span class="font-medium text-gray-900 group-hover:text-[var(--primary-color)]">
                        {{ article.title }}
                    </span>
                    <ArrowRightIcon class="h-4 w-4 text-gray-400 transition-transform group-hover:translate-x-1" />
                </Link>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!navigation?.length && !featuredArticles.length" class="text-center">
            <BookOpenIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">No content yet</h3>
            <p class="mt-2 text-gray-500">
                This knowledgebase doesn't have any articles yet.
            </p>
        </div>
    </KbLayout>
</template>

<style scoped>
:deep([style*="--primary-color"]) {
    --primary-color: v-bind('project.primary_color');
}
</style>
