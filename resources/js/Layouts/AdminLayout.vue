<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import {
    Bars3Icon,
    XMarkIcon,
    HomeIcon,
    FolderIcon,
    UsersIcon,
    DocumentTextIcon,
    Cog6ToothIcon,
} from '@heroicons/vue/24/outline';

const showingSidebar = ref(true);
const page = usePage();

const navigation = [
    { name: 'Dashboard', href: route('admin.dashboard'), icon: HomeIcon, current: route().current('admin.dashboard') },
    { name: 'Projects', href: route('admin.projects.index'), icon: FolderIcon, current: route().current('admin.projects.*') },
    { name: 'Users', href: route('admin.users.index'), icon: UsersIcon, current: route().current('admin.users.*') },
];
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside
            :class="[showingSidebar ? 'translate-x-0' : '-translate-x-full']"
            class="fixed inset-y-0 left-0 z-50 w-64 transform bg-gray-900 transition-transform duration-300 ease-in-out lg:translate-x-0"
        >
            <div class="flex h-16 items-center justify-between px-4">
                <Link :href="route('admin.dashboard')" class="text-xl font-bold text-white">
                    KB Admin
                </Link>
                <button @click="showingSidebar = false" class="lg:hidden text-gray-400 hover:text-white">
                    <XMarkIcon class="h-6 w-6" />
                </button>
            </div>
            <nav class="mt-4 px-2">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        item.current
                            ? 'bg-gray-800 text-white'
                            : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                        'group flex items-center rounded-md px-3 py-2 text-sm font-medium',
                    ]"
                >
                    <component
                        :is="item.icon"
                        :class="[
                            item.current ? 'text-white' : 'text-gray-400 group-hover:text-white',
                            'mr-3 h-5 w-5 flex-shrink-0',
                        ]"
                    />
                    {{ item.name }}
                </Link>
            </nav>
            <div class="absolute bottom-0 w-full border-t border-gray-700 p-4">
                <Link
                    :href="route('kb.index')"
                    class="flex items-center text-gray-300 hover:text-white"
                >
                    <DocumentTextIcon class="mr-3 h-5 w-5" />
                    View Knowledgebase
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top navbar -->
            <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button @click="showingSidebar = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <Bars3Icon class="h-6 w-6" />
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-x-2 text-sm">
                                    <span class="hidden lg:block">{{ $page.props.auth.user.name }}</span>
                                    <img
                                        v-if="$page.props.auth.user.avatar"
                                        :src="$page.props.auth.user.avatar"
                                        class="h-8 w-8 rounded-full"
                                    />
                                    <span
                                        v-else
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-500 text-sm font-medium text-white"
                                    >
                                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </button>
                            </template>
                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
