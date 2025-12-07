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
import Card from '@/Components/Card.vue';

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
                <!-- Article Header -->
                <header class="mb-8">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        {{ article.title }}
                    </h1>
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span>By {{ article.author.name }}</span>
                        <span class="text-gray-300">|</span>
                        <span>Published on {{ formatDate(article.published_at) }}</span>
                        <span class="text-gray-300">|</span>
                        <span class="flex items-center gap-1">
                            <ClockIcon class="h-4 w-4" />
                            {{ article.reading_time || 1 }} min read
                        </span>
                    </div>
                </header>

                <!-- Article Content -->
                <div
                    class="prose prose-lg max-w-none"
                    v-html="article.content"
                ></div>

                <!-- Feedback Section -->
                <div class="mt-12 border-t border-gray-200 pt-8">
                    <div class="text-center">
                        <p class="text-lg font-medium text-gray-900">Was this article helpful?</p>
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
                <div class="mt-12 grid gap-8 sm:grid-cols-2">
                    <Card
                        v-if="prevArticle"
                        :title="prevArticle.title"
                        description="Previous"
                        :href="route('kb.article', [project.slug, prevArticle.slug])"
                    />
                    <div v-else></div>
                    <Card
                        v-if="nextArticle"
                        :title="nextArticle.title"
                        description="Next"
                        :href="route('kb.article', [project.slug, nextArticle.slug])"
                    />
                </div>
            </article>

            <!-- Table of Contents -->
            <aside
                v-if="article.table_of_contents && article.table_of_contents.length > 0"
                class="hidden w-64 flex-shrink-0 lg:block"
            >
                <div class="sticky top-24">
                    <h4 class="text-sm font-semibold text-gray-900">
                        On this page
                    </h4>
                    <nav class="mt-4 space-y-2">
                        <a
                            v-for="item in article.table_of_contents"
                            :key="item.id"
                            :href="'#' + item.id"
                            :class="[
                                activeHeading === item.id
                                    ? 'text-primary-500 font-medium'
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
                        <h4 class="text-sm font-semibold text-gray-900">
                            Related articles
                        </h4>
                        <div class="mt-4 space-y-3">
                            <Link
                                v-for="related in relatedArticles"
                                :key="related.id"
                                :href="route('kb.article', [project.slug, related.slug])"
                                class="block text-sm text-gray-600 hover:text-primary-500"
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
