<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

interface Project {
    id: number;
    name: string;
    slug: string;
    description: string;
}

const props = defineProps<{
    projects: Project[];
}>();

const searchQuery = ref('');

const filteredProjects = computed(() => {
    if (!searchQuery.value) {
        return props.projects;
    }
    return props.projects.filter((project) =>
        project.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Knowledgebase" />

        <div class="bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                        Documentation
                    </h1>
                    <p class="mt-4 text-xl text-gray-600">
                        Explore our comprehensive guides and resources.
                    </p>
                </div>

                <div class="mx-auto mt-12 max-w-2xl">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="search"
                            name="search"
                            id="search"
                            class="block w-full rounded-full border-gray-300 bg-white py-3 pl-10 pr-3 text-lg placeholder-gray-500 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            placeholder="Search for articles..."
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="filteredProjects.length > 0" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="project in filteredProjects"
                        :key="project.id"
                        :title="project.name"
                        :description="project.description"
                        :href="route('kb.show', project.slug)"
                    />
                </div>
                <div v-else class="text-center">
                    <p class="text-lg text-gray-600">No projects found.</p>
                </div>
            </div>
        </div>

        <footer class="border-t border-gray-200 bg-gray-50">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">&copy; {{ new Date().getFullYear() }} Your Company, Inc.</p>
                    <div class="flex space-x-6">
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-600">About</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-600">Contact</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-600">Privacy Policy</a>
                    </div>
                </div>
            </div>
        </footer>
    </AuthenticatedLayout>
</template>
