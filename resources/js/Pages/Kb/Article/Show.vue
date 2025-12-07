<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import KbLayout from '@/Layouts/KbLayout.vue';
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    HandThumbUpIcon,
    HandThumbDownIcon,
    ClockIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';
import {
    HandThumbUpIcon as HandThumbUpSolidIcon,
    HandThumbDownIcon as HandThumbDownSolidIcon,
} from '@heroicons/vue/24/solid';

interface TocItem {
    level: number;
    text: string;
    id: string;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    table_of_contents: TocItem[] | null;
    views: number;
    helpful_count: number;
    not_helpful_count: number;
    reading_time: number;
    published_at: string;
    author: { name: string; avatar: string | null };
    group: { name: string; slug: string } | null;
}

interface BreadcrumbItem {
    id: number;
    name: string;
    slug: string;
    type?: string;
}

interface NavArticle {
    title: string;
    slug: string;
}

interface Project {
    id: number;
    name: string;
    slug: string;
    description: string;
    primary_color: string;
    logo_url: string | null;
}

const props = defineProps<{
    project: Project;
    article: Article;
    navigation: any[];
    breadcrumb: BreadcrumbItem[];
    relatedArticles: Article[];
    prevArticle: NavArticle | null;
    nextArticle: NavArticle | null;
}>();

const feedbackSubmitted = ref(false);
const feedbackValue = ref<boolean | null>(null);
const activeHeading = ref('');

const submitFeedback = async (isHelpful: boolean) => {
    if (feedbackSubmitted.value) return;

    try {
        await fetch(route('kb.feedback', [props.project.slug, props.article.slug]), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ is_helpful: isHelpful }),
        });
        feedbackValue.value = isHelpful;
        feedbackSubmitted.value = true;
    } catch (e) {
        console.error(e);
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeHeading.value = entry.target.id;
                }
            });
        },
        { rootMargin: '-80px 0px -80% 0px' }
    );

    document.querySelectorAll('h2[id], h3[id], h4[id]').forEach((heading) => {
        observer.observe(heading);
    });
});
</script>

<template>
    <KbLayout :project="project" :navigation="navigation">
        <Head :title="`${article.title} - ${project.name}`" />

        <div class="lg:flex lg:gap-8">
            <!-- Main Content -->
            <article class="min-w-0 flex-1">
                <!-- Breadcrumb -->
                <nav class="mb-6 flex items-center gap-2 text-sm text-gray-500">
                    <Link :href="route('kb.show', project.slug)" class="hover:text-gray-900">
                        {{ project.name }}
                    </Link>
                    <template v-for="(item, index) in breadcrumb" :key="item.id">
                        <span>/</span>
                        <Link
                            v-if="item.type !== 'article'"
                            :href="route('kb.group', [project.slug, item.slug])"
                            class="hover:text-gray-900"
                        >
                            {{ item.name }}
                        </Link>
                        <span v-else class="text-gray-900">{{ item.name }}</span>
                    </template>
                </nav>

                <!-- Article Header -->
                <header class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                        {{ article.title }}
                    </h1>
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <CalendarIcon class="h-4 w-4" />
                            {{ formatDate(article.published_at) }}
                        </span>
                        <span class="flex items-center gap-1">
                            <ClockIcon class="h-4 w-4" />
                            {{ article.reading_time || 1 }} min read
                        </span>
                        <span>By {{ article.author.name }}</span>
                    </div>
                </header>

                <!-- Article Content -->
                <div
                    class="prose prose-gray max-w-none prose-headings:scroll-mt-20 prose-a:text-[var(--primary-color)] prose-a:no-underline hover:prose-a:underline"
                    v-html="article.content"
                ></div>

                <!-- Feedback Section -->
                <div class="mt-12 border-t border-gray-200 pt-8">
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-900">Was this article helpful?</p>
                        <div class="mt-4 flex justify-center gap-4">
                            <button
                                @click="submitFeedback(true)"
                                :disabled="feedbackSubmitted"
                                :class="[
                                    feedbackValue === true
                                        ? 'border-green-500 bg-green-50 text-green-700'
                                        : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                                    'inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition-colors disabled:cursor-not-allowed'
                                ]"
                            >
                                <component
                                    :is="feedbackValue === true ? HandThumbUpSolidIcon : HandThumbUpIcon"
                                    class="h-5 w-5"
                                />
                                Yes
                            </button>
                            <button
                                @click="submitFeedback(false)"
                                :disabled="feedbackSubmitted"
                                :class="[
                                    feedbackValue === false
                                        ? 'border-red-500 bg-red-50 text-red-700'
                                        : 'border-gray-300 text-gray-700 hover:bg-gray-50',
                                    'inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition-colors disabled:cursor-not-allowed'
                                ]"
                            >
                                <component
                                    :is="feedbackValue === false ? HandThumbDownSolidIcon : HandThumbDownIcon"
                                    class="h-5 w-5"
                                />
                                No
                            </button>
                        </div>
                        <p v-if="feedbackSubmitted" class="mt-3 text-sm text-gray-500">
                            Thanks for your feedback!
                        </p>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="mt-12 grid gap-4 border-t border-gray-200 pt-8 sm:grid-cols-2">
                    <Link
                        v-if="prevArticle"
                        :href="route('kb.article', [project.slug, prevArticle.slug])"
                        class="group flex items-center gap-4 rounded-lg border border-gray-200 p-4 hover:border-gray-300 hover:shadow-sm"
                    >
                        <ChevronLeftIcon class="h-5 w-5 text-gray-400" />
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-500">Previous</p>
                            <p class="truncate text-sm font-medium text-gray-900 group-hover:text-[var(--primary-color)]">
                                {{ prevArticle.title }}
                            </p>
                        </div>
                    </Link>
                    <div v-else></div>
                    <Link
                        v-if="nextArticle"
                        :href="route('kb.article', [project.slug, nextArticle.slug])"
                        class="group flex items-center justify-end gap-4 rounded-lg border border-gray-200 p-4 text-right hover:border-gray-300 hover:shadow-sm"
                    >
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-500">Next</p>
                            <p class="truncate text-sm font-medium text-gray-900 group-hover:text-[var(--primary-color)]">
                                {{ nextArticle.title }}
                            </p>
                        </div>
                        <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                    </Link>
                </div>
            </article>

            <!-- Table of Contents -->
            <aside
                v-if="article.table_of_contents && article.table_of_contents.length > 0"
                class="hidden w-64 flex-shrink-0 lg:block"
            >
                <div class="sticky top-24">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                        On this page
                    </h4>
                    <nav class="mt-4 space-y-2">
                        <a
                            v-for="item in article.table_of_contents"
                            :key="item.id"
                            :href="'#' + item.id"
                            :class="[
                                activeHeading === item.id
                                    ? 'text-[var(--primary-color)] font-medium'
                                    : 'text-gray-600 hover:text-gray-900',
                                item.level === 3 ? 'pl-4' : '',
                                item.level === 4 ? 'pl-8' : '',
                                'block text-sm transition-colors'
                            ]"
                        >
                            {{ item.text }}
                        </a>
                    </nav>

                    <!-- Related Articles -->
                    <div v-if="relatedArticles.length > 0" class="mt-8 border-t border-gray-200 pt-6">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Related articles
                        </h4>
                        <div class="mt-4 space-y-3">
                            <Link
                                v-for="related in relatedArticles"
                                :key="related.id"
                                :href="route('kb.article', [project.slug, related.slug])"
                                class="block text-sm text-gray-600 hover:text-[var(--primary-color)]"
                            >
                                {{ related.title }}
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </KbLayout>
</template>

<style scoped>
:deep([style*="--primary-color"]) {
    --primary-color: v-bind('project.primary_color');
}

:deep(.prose img) {
    border-radius: 0.5rem;
    border: 1px solid rgb(229 231 235);
}

:deep(.prose pre) {
    border-radius: 0.5rem;
    background-color: rgb(17 24 39);
}

:deep(.prose code:not(pre code)) {
    border-radius: 0.25rem;
    background-color: rgb(243 244 246);
    padding: 0.125rem 0.375rem;
    font-size: 0.875rem;
    font-weight: 400;
    color: rgb(31 41 55);
}

:deep(.prose code:not(pre code))::before,
:deep(.prose code:not(pre code))::after {
    content: '';
}
</style>
