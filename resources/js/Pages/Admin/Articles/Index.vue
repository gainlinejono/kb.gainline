<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, EyeIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

interface Article {
    id: number;
    title: string;
    slug: string;
    status: string;
    views: number;
    published_at: string | null;
    author: { name: string };
    group: { name: string } | null;
}

interface Group {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Project {
    id: number;
    name: string;
    slug: string;
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
    project: Project;
    articles: Pagination<Article>;
    groups: Group[];
    filters: { search?: string; status?: string; group_id?: string };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const groupId = ref(props.filters.group_id || '');

const applyFilters = () => {
    router.get(route('admin.projects.articles.index', props.project.slug), {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        group_id: groupId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const debouncedSearch = debounce(applyFilters, 300);

watch(search, debouncedSearch);
watch([status, groupId], applyFilters);

const deleteArticle = (article: Article) => {
    if (confirm(`Are you sure you want to delete "${article.title}"?`)) {
        router.delete(route('admin.projects.articles.destroy', [props.project.slug, article.id]));
    }
};

const togglePublish = (article: Article) => {
    const route_name = article.status === 'published'
        ? 'admin.projects.articles.unpublish'
        : 'admin.projects.articles.publish';
    router.post(route(route_name, [props.project.slug, article.id]));
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Articles - ${project.name}`" />

        <div class="mb-6">
            <Link :href="route('admin.projects.edit', project.slug)" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to {{ project.name }}
            </Link>
        </div>

        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Articles</h1>
                <p class="mt-1 text-sm text-gray-600">Manage articles for {{ project.name }}</p>
            </div>
            <Link
                :href="route('admin.projects.articles.create', project.slug)"
                class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:mt-0"
            >
                <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" />
                New Article
            </Link>
        </div>

        <!-- Filters -->
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search articles..."
                    class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                />
            </div>
            <select
                v-model="status"
                class="block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"
            >
                <option value="all">All statuses</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
            </select>
            <select
                v-model="groupId"
                class="block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"
            >
                <option value="">All groups</option>
                <option v-for="group in groups" :key="group.id" :value="group.id">
                    {{ group.parent_id ? '-- ' : '' }}{{ group.name }}
                </option>
            </select>
        </div>

        <!-- Articles Table -->
        <div class="mt-6 overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Title
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Group
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Views
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Published
                        </th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="article in articles.data" :key="article.id">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ article.title }}</p>
                                <p class="text-sm text-gray-500">by {{ article.author.name }}</p>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ article.group?.name || '-' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <button
                                @click="togglePublish(article)"
                                :class="[
                                    article.status === 'published' ? 'bg-green-100 text-green-800 hover:bg-green-200' :
                                    article.status === 'draft' ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' :
                                    'bg-gray-100 text-gray-800 hover:bg-gray-200',
                                    'inline-flex rounded-full px-2 text-xs font-semibold leading-5'
                                ]"
                            >
                                {{ article.status }}
                            </button>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ article.views.toLocaleString() }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ formatDate(article.published_at) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <Link
                                    v-if="article.status === 'published'"
                                    :href="route('kb.article', [project.slug, article.slug])"
                                    target="_blank"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <EyeIcon class="h-5 w-5" />
                                </Link>
                                <Link
                                    :href="route('admin.projects.articles.edit', [project.slug, article.id])"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <button
                                    @click="deleteArticle(article)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="articles.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                            No articles found.
                            <Link :href="route('admin.projects.articles.create', project.slug)" class="text-indigo-600 hover:text-indigo-500">
                                Create your first article
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav v-if="articles.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-700">
                        Showing {{ (articles.current_page - 1) * articles.per_page + 1 }} to
                        {{ Math.min(articles.current_page * articles.per_page, articles.total) }} of
                        {{ articles.total }} results
                    </p>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                        <Link
                            v-for="link in articles.links"
                            :key="link.label"
                            :href="link.url || ''"
                            :class="[
                                link.active ? 'z-10 bg-indigo-600 text-white' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
                                !link.url ? 'cursor-not-allowed opacity-50' : '',
                                'relative inline-flex items-center px-4 py-2 text-sm font-semibold'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </nav>
        </div>
    </AdminLayout>
</template>
