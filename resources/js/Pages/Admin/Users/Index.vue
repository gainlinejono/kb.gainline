<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { PlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline';
import { debounce } from 'lodash-es';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    projects_count: number;
    created_at: string;
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
    users: Pagination<User>;
    filters: { search?: string; role?: string };
}>();

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');

const applyFilters = () => {
    router.get(route('admin.users.index'), {
        search: search.value || undefined,
        role: role.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const debouncedSearch = debounce(applyFilters, 300);
watch(search, debouncedSearch);
watch(role, applyFilters);

const deleteUser = (user: User) => {
    if (confirm(`Are you sure you want to delete "${user.name}"?`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <AdminLayout>
        <Head title="Users" />

        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Users</h1>
                <p class="mt-1 text-sm text-gray-600">Manage user accounts</p>
            </div>
            <Link
                :href="route('admin.users.create')"
                class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:mt-0"
            >
                <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" />
                New User
            </Link>
        </div>

        <!-- Filters -->
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search users..."
                    class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                />
            </div>
            <select
                v-model="role"
                class="block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"
            >
                <option value="">All users</option>
                <option value="admin">Admins only</option>
            </select>
        </div>

        <!-- Users Table -->
        <div class="mt-6 overflow-hidden rounded-lg bg-white shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Projects
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                            Joined
                        </th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <tr v-for="user in users.data" :key="user.id">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gray-500 text-sm font-medium text-white">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900">{{ user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span
                                v-if="user.is_admin"
                                class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800"
                            >
                                <ShieldCheckIcon class="h-3.5 w-3.5" />
                                Admin
                            </span>
                            <span v-else class="text-sm text-gray-500">User</span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ user.projects_count }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ formatDate(user.created_at) }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                            <div class="flex justify-end gap-2">
                                <Link
                                    :href="route('admin.users.edit', user.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    <PencilIcon class="h-5 w-5" />
                                </Link>
                                <button
                                    @click="deleteUser(user)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <TrashIcon class="h-5 w-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.data.length === 0">
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                            No users found.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav v-if="users.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-700">
                        Showing {{ (users.current_page - 1) * users.per_page + 1 }} to
                        {{ Math.min(users.current_page * users.per_page, users.total) }} of
                        {{ users.total }} results
                    </p>
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                        <Link
                            v-for="link in users.links"
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
