<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRightIcon } from '@heroicons/vue/24/outline';

defineProps<{
    title: string;
    description: string;
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="group relative block rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:border-primary-500/50 hover:shadow-lg"
    >
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ title }}
                </h3>
                <p v-if="description" class="mt-2 text-sm text-gray-600">
                    {{ description }}
                </p>
            </div>
            <div class="ml-4 flex-shrink-0">
                <ArrowUpRightIcon class="h-6 w-6 text-gray-400 transition-colors group-hover:text-primary-500" />
            </div>
        </div>
    </Link>
</template>
