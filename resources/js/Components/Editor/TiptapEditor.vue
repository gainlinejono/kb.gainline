<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Youtube from '@tiptap/extension-youtube';
import Highlight from '@tiptap/extension-highlight';
import {
    BoldIcon,
    ItalicIcon,
    ListBulletIcon,
    CodeBracketIcon,
    PhotoIcon,
    LinkIcon,
    VideoCameraIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
    projectId?: number;
    articleId?: number;
}>();

const emit = defineEmits(['update:modelValue']);

const showImageModal = ref(false);
const showVideoModal = ref(false);
const showLinkModal = ref(false);
const imageUrl = ref('');
const videoUrl = ref('');
const linkUrl = ref('');
const uploadingImage = ref(false);

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [2, 3, 4],
            },
        }),
        Image.configure({
            HTMLAttributes: {
                class: 'rounded-lg max-w-full',
            },
        }),
        Link.configure({
            openOnClick: false,
            HTMLAttributes: {
                class: 'text-indigo-600 hover:text-indigo-500 underline',
            },
        }),
        Placeholder.configure({
            placeholder: props.placeholder || 'Start writing...',
        }),
        Youtube.configure({
            width: 640,
            height: 360,
            HTMLAttributes: {
                class: 'rounded-lg overflow-hidden',
            },
        }),
        Highlight,
    ],
    content: props.modelValue,
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose max-w-none focus:outline-none min-h-[300px] p-4',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
    },
});

watch(() => props.modelValue, (value) => {
    if (editor.value && editor.value.getHTML() !== value) {
        editor.value.commands.setContent(value, { emitUpdate: false });
    }
});

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const addImage = async () => {
    if (imageUrl.value) {
        editor.value?.chain().focus().setImage({ src: imageUrl.value }).run();
        imageUrl.value = '';
        showImageModal.value = false;
    }
};

const uploadImage = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    uploadingImage.value = true;
    const formData = new FormData();
    formData.append('image', input.files[0]);
    if (props.articleId) formData.append('article_id', props.articleId.toString());
    if (props.projectId) formData.append('project_id', props.projectId.toString());

    try {
        const response = await fetch(route('admin.media.image'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
        });
        const data = await response.json();
        if (data.url) {
            editor.value?.chain().focus().setImage({ src: data.url }).run();
        }
    } catch (e) {
        console.error(e);
    } finally {
        uploadingImage.value = false;
        showImageModal.value = false;
    }
};

const addVideo = () => {
    if (videoUrl.value) {
        editor.value?.chain().focus().setYoutubeVideo({ src: videoUrl.value }).run();
        videoUrl.value = '';
        showVideoModal.value = false;
    }
};

const setLink = () => {
    if (linkUrl.value) {
        editor.value?.chain().focus().setLink({ href: linkUrl.value }).run();
        linkUrl.value = '';
        showLinkModal.value = false;
    }
};

const removeLink = () => {
    editor.value?.chain().focus().unsetLink().run();
};
</script>

<template>
    <div class="rounded-lg border border-gray-300 bg-white">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 p-2">
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-gray-200': editor?.isActive('heading', { level: 2 }) }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Heading 2"
            >
                <span class="text-sm font-bold">H2</span>
            </button>
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ 'bg-gray-200': editor?.isActive('heading', { level: 3 }) }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Heading 3"
            >
                <span class="text-sm font-bold">H3</span>
            </button>

            <div class="mx-1 h-6 w-px bg-gray-300"></div>

            <button
                @click="editor?.chain().focus().toggleBold().run()"
                :class="{ 'bg-gray-200': editor?.isActive('bold') }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Bold"
            >
                <BoldIcon class="h-5 w-5" />
            </button>
            <button
                @click="editor?.chain().focus().toggleItalic().run()"
                :class="{ 'bg-gray-200': editor?.isActive('italic') }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Italic"
            >
                <ItalicIcon class="h-5 w-5" />
            </button>
            <button
                @click="editor?.chain().focus().toggleHighlight().run()"
                :class="{ 'bg-gray-200': editor?.isActive('highlight') }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Highlight"
            >
                <span class="rounded bg-yellow-200 px-1 text-sm">A</span>
            </button>

            <div class="mx-1 h-6 w-px bg-gray-300"></div>

            <button
                @click="editor?.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-gray-200': editor?.isActive('bulletList') }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Bullet List"
            >
                <ListBulletIcon class="h-5 w-5" />
            </button>
            <button
                @click="editor?.chain().focus().toggleOrderedList().run()"
                :class="{ 'bg-gray-200': editor?.isActive('orderedList') }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Numbered List"
            >
                <span class="text-sm font-medium">1.</span>
            </button>
            <button
                @click="editor?.chain().focus().toggleCodeBlock().run()"
                :class="{ 'bg-gray-200': editor?.isActive('codeBlock') }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Code Block"
            >
                <CodeBracketIcon class="h-5 w-5" />
            </button>

            <div class="mx-1 h-6 w-px bg-gray-300"></div>

            <button
                @click="showImageModal = true"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Insert Image"
            >
                <PhotoIcon class="h-5 w-5" />
            </button>
            <button
                @click="showVideoModal = true"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Insert Video"
            >
                <VideoCameraIcon class="h-5 w-5" />
            </button>
            <button
                @click="showLinkModal = true"
                :class="{ 'bg-gray-200': editor?.isActive('link') }"
                class="rounded p-2 hover:bg-gray-100"
                type="button"
                title="Insert Link"
            >
                <LinkIcon class="h-5 w-5" />
            </button>
            <button
                v-if="editor?.isActive('link')"
                @click="removeLink"
                class="rounded p-2 text-red-600 hover:bg-red-50"
                type="button"
                title="Remove Link"
            >
                <span class="text-xs">Unlink</span>
            </button>
        </div>

        <!-- Editor -->
        <EditorContent :editor="editor" />

        <!-- Image Modal -->
        <div v-if="showImageModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-lg bg-white p-6">
                <h3 class="mb-4 text-lg font-medium">Insert Image</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Upload Image</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="uploadImage"
                            :disabled="uploadingImage"
                            class="mt-1 block w-full text-sm"
                        />
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-white px-2 text-gray-500">or</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Image URL</label>
                        <input
                            v-model="imageUrl"
                            type="url"
                            placeholder="https://..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showImageModal = false"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="addImage"
                        :disabled="!imageUrl"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                    >
                        Insert
                    </button>
                </div>
            </div>
        </div>

        <!-- Video Modal -->
        <div v-if="showVideoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-lg bg-white p-6">
                <h3 class="mb-4 text-lg font-medium">Insert YouTube Video</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700">YouTube URL</label>
                    <input
                        v-model="videoUrl"
                        type="url"
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showVideoModal = false"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="addVideo"
                        :disabled="!videoUrl"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                    >
                        Insert
                    </button>
                </div>
            </div>
        </div>

        <!-- Link Modal -->
        <div v-if="showLinkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-lg bg-white p-6">
                <h3 class="mb-4 text-lg font-medium">Insert Link</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700">URL</label>
                    <input
                        v-model="linkUrl"
                        type="url"
                        placeholder="https://..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        @click="showLinkModal = false"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="setLink"
                        :disabled="!linkUrl"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                    >
                        Insert
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #adb5bd;
    pointer-events: none;
    height: 0;
}
</style>
