<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import KbLayout from '@/Layouts/KbLayout.vue';
import {
    DocumentTextIcon,
    BookOpenIcon,
    ClockIcon,
    ArrowRightIcon,
} from '@heroicons/vue/24/outline';
import Card from '@/Components/Card.vue';

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
        <div class="mb-12 text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                {{ project.name }}
            </h1>
            <p v-if="project.description" class="mt-4 text-xl text-gray-600">
                {{ project.description }}
            </p>
        </div>

        <!-- Getting Started -->
        <div v-if="navigation && navigation.length > 0" class="mb-12">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">Getting Started</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <Card
                    v-for="group in navigation.slice(0, 6)"
                    :key="group.id"
                    :title="group.name"
                    :description="`${group.articles.length} article${group.articles.length !== 1 ? 's' : ''}`"
                    :href="route('kb.group', [project.slug, group.path])"
                />
            </div>
        </div>

        <!-- Popular Articles -->
        <div v-if="featuredArticles.length > 0" class="mb-12">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">Popular Articles</h2>
            <div class="grid gap-6 sm:grid-cols-2">
                <Card
                    v-for="article in featuredArticles"
                    :key="article.id"
                    :title="article.title"
                    :description="article.excerpt"
                    :href="route('kb.article', [project.slug, article.slug])"
                />
            </div>
        </div>

        <!-- Recently Updated -->
        <div v-if="recentArticles.length > 0">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">Recently Updated</h2>
            <div class="space-y-4">
                <Card
                    v-for="article in recentArticles"
                    :key="article.id"
                    :title="article.title"
                    :description="`Updated on ${new Date(article.published_at).toLocaleDateString()}`"
                    :href="route('kb.article', [project.slug, article.slug])"
                />
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
