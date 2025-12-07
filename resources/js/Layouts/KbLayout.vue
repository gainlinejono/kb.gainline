<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import {
    Bars3Icon,
    XMarkIcon,
    MagnifyingGlassIcon,
    ChevronRightIcon,
    ChevronDownIcon,
} from '@heroicons/vue/24/outline';

interface NavigationItem {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    path: string;
    children: NavigationItem[];
    articles: Array<{ id: number; title: string; slug: string }>;
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
    navigation?: NavigationItem[];
}>();

const page = usePage();
const showMobileMenu = ref(false);
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const showSearchResults = ref(false);
const expandedGroups = ref<number[]>([]);

const toggleGroup = (groupId: number) => {
    const index = expandedGroups.value.indexOf(groupId);
    if (index > -1) {
        expandedGroups.value.splice(index, 1);
    } else {
        expandedGroups.value.push(groupId);
    }
};

const isGroupExpanded = (groupId: number) => expandedGroups.value.includes(groupId);

const search = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }

    try {
        const response = await fetch(route('kb.search', props.project.slug) + '?q=' + encodeURIComponent(searchQuery.value));
        const data = await response.json();
        searchResults.value = data.results;
        showSearchResults.value = true;
    } catch (e) {
        console.error(e);
    }
};

const hideSearchResultsDelayed = () => {
    setTimeout(() => {
        showSearchResults.value = false;
    }, 200);
};

const goToArticle = (slug: string) => {
    showSearchResults.value = false;
    searchQuery.value = '';
    router.visit(route('kb.article', [props.project.slug, slug]));
};

const primaryColorStyle = computed(() => ({
    '--primary-color': props.project.primary_color || '#3B82F6',
}));
</script>

<template>
    <div class="min-h-screen bg-white" :style="primaryColorStyle">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-gray-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-4">
                    <button @click="showMobileMenu = true" class="lg:hidden">
                        <Bars3Icon class="h-6 w-6 text-gray-500" />
                    </button>
                    <Link :href="route('kb.show', project.slug)" class="flex items-center gap-3">
                        <img
                            v-if="project.logo_url"
                            :src="project.logo_url"
                            :alt="project.name"
                            class="h-8 w-8 rounded"
                        />
                        <span
                            v-else
                            class="flex h-8 w-8 items-center justify-center rounded text-sm font-bold text-white"
                            :style="{ backgroundColor: project.primary_color }"
                        >
                            {{ project.name.charAt(0) }}
                        </span>
                        <span class="text-lg font-semibold text-gray-900">{{ project.name }}</span>
                    </Link>
                </div>

                <!-- Search -->
                <div class="relative hidden w-96 md:block">
                    <div class="relative">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            @input="search"
                            @focus="searchQuery.length >= 2 && (showSearchResults = true)"
                            @blur="hideSearchResultsDelayed"
                            type="search"
                            placeholder="Search documentation..."
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2 pl-10 pr-4 text-sm focus:border-[var(--primary-color)] focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)]"
                        />
                    </div>
                    <!-- Search Results Dropdown -->
                    <div
                        v-if="showSearchResults && searchResults.length > 0"
                        class="absolute left-0 right-0 top-full mt-2 max-h-80 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                    >
                        <div
                            v-for="result in searchResults"
                            :key="result.id"
                            @click="goToArticle(result.slug)"
                            class="cursor-pointer border-b border-gray-100 p-4 last:border-0 hover:bg-gray-50"
                        >
                            <h4 class="font-medium text-gray-900">{{ result.title }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ result.excerpt }}</p>
                        </div>
                    </div>
                    <div
                        v-else-if="showSearchResults && searchQuery.length >= 2"
                        class="absolute left-0 right-0 top-full mt-2 rounded-lg border border-gray-200 bg-white p-4 text-center text-sm text-gray-500 shadow-lg"
                    >
                        No results found
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user?.is_admin"
                        :href="route('admin.dashboard')"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Admin
                    </Link>
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-500 text-sm font-medium text-white"
                                >
                                    {{ $page.props.auth.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                                </span>
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('kb.index')">All Projects</DropdownLink>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl">
            <div class="lg:flex">
                <!-- Sidebar Navigation -->
                <aside
                    v-if="navigation && navigation.length > 0"
                    :class="[
                        showMobileMenu ? 'translate-x-0' : '-translate-x-full',
                        'fixed inset-y-0 left-0 z-40 w-72 transform overflow-y-auto bg-white p-4 transition-transform duration-300 lg:relative lg:translate-x-0 lg:border-r lg:border-gray-200'
                    ]"
                >
                    <!-- Mobile close button -->
                    <button
                        @click="showMobileMenu = false"
                        class="absolute right-4 top-4 lg:hidden"
                    >
                        <XMarkIcon class="h-6 w-6 text-gray-500" />
                    </button>

                    <nav class="mt-12 lg:mt-0">
                        <template v-for="group in navigation" :key="group.id">
                            <!-- Group Header -->
                            <div class="mb-2">
                                <button
                                    @click="toggleGroup(group.id)"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold text-gray-900 hover:bg-gray-100"
                                >
                                    <span class="flex items-center gap-2">
                                        <span v-if="group.icon" v-html="group.icon"></span>
                                        {{ group.name }}
                                    </span>
                                    <component
                                        :is="isGroupExpanded(group.id) ? ChevronDownIcon : ChevronRightIcon"
                                        class="h-4 w-4 text-gray-400"
                                    />
                                </button>

                                <!-- Group Content -->
                                <div v-show="isGroupExpanded(group.id)" class="ml-4 mt-1 space-y-1">
                                    <!-- Articles -->
                                    <Link
                                        v-for="article in group.articles"
                                        :key="article.id"
                                        :href="route('kb.article', [project.slug, article.slug])"
                                        class="block rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                    >
                                        {{ article.title }}
                                    </Link>

                                    <!-- Sub-groups -->
                                    <template v-for="subgroup in group.children" :key="subgroup.id">
                                        <button
                                            @click="toggleGroup(subgroup.id)"
                                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                        >
                                            {{ subgroup.name }}
                                            <component
                                                :is="isGroupExpanded(subgroup.id) ? ChevronDownIcon : ChevronRightIcon"
                                                class="h-3 w-3 text-gray-400"
                                            />
                                        </button>
                                        <div v-show="isGroupExpanded(subgroup.id)" class="ml-4 space-y-1">
                                            <Link
                                                v-for="article in subgroup.articles"
                                                :key="article.id"
                                                :href="route('kb.article', [project.slug, article.slug])"
                                                class="block rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 hover:text-gray-900"
                                            >
                                                {{ article.title }}
                                            </Link>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </nav>
                </aside>

                <!-- Main Content -->
                <main class="min-w-0 flex-1 px-4 py-8 sm:px-6 lg:px-8">
                    <slot />
                </main>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div
            v-if="showMobileMenu"
            @click="showMobileMenu = false"
            class="fixed inset-0 z-30 bg-gray-600/50 lg:hidden"
        ></div>
    </div>
</template>

<style scoped>
:deep(.router-link-active) {
    background-color: rgba(var(--primary-color-rgb), 0.1);
    color: var(--primary-color);
}
</style>
