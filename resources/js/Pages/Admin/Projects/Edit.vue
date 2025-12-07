<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Member {
    id: number;
    user_id: number;
    role: string;
    user: User;
}

interface Project {
    id: number;
    name: string;
    slug: string;
    description: string;
    primary_color: string;
    basecamp_project_id: string;
    is_active: boolean;
    logo_url: string | null;
    members: Member[];
}

const props = defineProps<{
    project: Project;
    availableUsers: User[];
}>();

const form = useForm({
    name: props.project.name,
    slug: props.project.slug,
    description: props.project.description || '',
    primary_color: props.project.primary_color || '#3B82F6',
    basecamp_project_id: props.project.basecamp_project_id || '',
    is_active: props.project.is_active,
    logo: null as File | null,
});

const memberForm = useForm({
    user_id: '',
    role: 'viewer',
});

const showAddMember = ref(false);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post(route('admin.projects.update', props.project.slug), {
        forceFormData: true,
    });
};

const handleLogoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.logo = target.files[0];
    }
};

const addMember = () => {
    memberForm.post(route('admin.projects.members.add', props.project.slug), {
        preserveScroll: true,
        onSuccess: () => {
            memberForm.reset();
            showAddMember.value = false;
        },
    });
};

const updateMemberRole = (member: Member, role: string) => {
    router.patch(route('admin.projects.members.update', [props.project.slug, member.user_id]), {
        role,
    }, {
        preserveScroll: true,
    });
};

const removeMember = (member: Member) => {
    if (confirm(`Remove ${member.user.name} from this project?`)) {
        router.delete(route('admin.projects.members.remove', [props.project.slug, member.user_id]), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit ${project.name}`" />

        <div class="mb-8">
            <Link :href="route('admin.projects.index')" class="text-sm text-indigo-600 hover:text-indigo-500">
                &larr; Back to Projects
            </Link>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit {{ project.name }}</h1>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Project Form -->
            <div class="lg:col-span-2">
                <form @submit.prevent="submit">
                    <div class="space-y-6 rounded-lg bg-white p-6 shadow">
                        <div>
                            <InputLabel for="name" value="Project Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="slug" value="Slug" />
                            <TextInput
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.slug" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            ></textarea>
                            <InputError :message="form.errors.description" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="primary_color" value="Primary Color" />
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    id="primary_color"
                                    v-model="form.primary_color"
                                    type="color"
                                    class="h-10 w-20 cursor-pointer rounded border border-gray-300"
                                />
                                <TextInput v-model="form.primary_color" type="text" class="w-32" />
                            </div>
                            <InputError :message="form.errors.primary_color" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="logo" value="Logo" />
                            <div v-if="project.logo_url" class="mb-2">
                                <img :src="project.logo_url" alt="Current logo" class="h-16 w-16 rounded object-cover" />
                            </div>
                            <input
                                id="logo"
                                type="file"
                                accept="image/*"
                                @change="handleLogoChange"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <InputError :message="form.errors.logo" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="basecamp_project_id" value="Basecamp Project ID" />
                            <TextInput
                                id="basecamp_project_id"
                                v-model="form.basecamp_project_id"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.basecamp_project_id" class="mt-2" />
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            />
                            <label for="is_active" class="ml-2 text-sm text-gray-900">Active</label>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <Link :href="route('admin.projects.index')" class="text-sm text-gray-600 hover:text-gray-900">
                            Cancel
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>

            <!-- Members Panel -->
            <div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Members</h3>
                        <button
                            @click="showAddMember = true"
                            class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500"
                        >
                            <PlusIcon class="mr-1 h-4 w-4" />
                            Add
                        </button>
                    </div>

                    <!-- Add Member Form -->
                    <div v-if="showAddMember" class="mt-4 space-y-3 rounded-lg bg-gray-50 p-4">
                        <div>
                            <select
                                v-model="memberForm.user_id"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Select user...</option>
                                <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="memberForm.role"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="viewer">Viewer</option>
                                <option value="editor">Editor</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="addMember"
                                class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500"
                            >
                                Add Member
                            </button>
                            <button
                                @click="showAddMember = false"
                                class="rounded px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Members List -->
                    <ul class="mt-4 divide-y divide-gray-200">
                        <li v-for="member in project.members" :key="member.id" class="py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ member.user.name }}</p>
                                    <p class="text-xs text-gray-500">{{ member.user.email }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select
                                        :value="member.role"
                                        @change="updateMemberRole(member, ($event.target as HTMLSelectElement).value)"
                                        class="rounded-md border-gray-300 py-1 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="viewer">Viewer</option>
                                        <option value="editor">Editor</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <button @click="removeMember(member)" class="text-red-500 hover:text-red-700">
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li v-if="project.members.length === 0" class="py-4 text-center text-sm text-gray-500">
                            No members yet
                        </li>
                    </ul>
                </div>

                <!-- Quick Links -->
                <div class="mt-6 rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-medium text-gray-900">Quick Links</h3>
                    <div class="mt-4 space-y-2">
                        <Link
                            :href="route('admin.projects.groups.index', project.slug)"
                            class="block rounded-lg border border-gray-200 p-3 hover:border-indigo-500 hover:bg-indigo-50"
                        >
                            <span class="text-sm font-medium text-gray-900">Manage Groups</span>
                        </Link>
                        <Link
                            :href="route('admin.projects.articles.index', project.slug)"
                            class="block rounded-lg border border-gray-200 p-3 hover:border-indigo-500 hover:bg-indigo-50"
                        >
                            <span class="text-sm font-medium text-gray-900">Manage Articles</span>
                        </Link>
                        <Link
                            :href="route('kb.show', project.slug)"
                            class="block rounded-lg border border-gray-200 p-3 hover:border-indigo-500 hover:bg-indigo-50"
                        >
                            <span class="text-sm font-medium text-gray-900">View Knowledgebase</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
