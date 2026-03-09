<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Insight {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    preview_chars: number;
    cover_image_url: string | null;
    tags: string[] | null;
    is_premium: boolean;
    is_published: boolean;
    published_at: string | null;
}

interface Props {
    insight: Insight;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Insights', href: '/admin/insights' },
    { title: 'Edit', href: `/admin/insights/${props.insight.id}/edit` },
];

const publishedAtLocal = computed(() => {
    if (!props.insight.published_at) return '';
    const date = new Date(props.insight.published_at);
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(
        date.getDate(),
    ).padStart(2, '0')}T${String(date.getHours()).padStart(2, '0')}:${String(
        date.getMinutes(),
    ).padStart(2, '0')}`;
});

const form = useForm({
    title: props.insight.title,
    slug: props.insight.slug,
    excerpt: props.insight.excerpt,
    content: props.insight.content,
    cover_image_url: props.insight.cover_image_url ?? '',
    tags_input: (props.insight.tags ?? []).join(', '),
    preview_chars: props.insight.preview_chars,
    is_premium: props.insight.is_premium,
    is_published: props.insight.is_published,
    published_at: publishedAtLocal.value,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        tags: data.tags_input
            ? data.tags_input.split(',').map((tag) => tag.trim()).filter(Boolean)
            : [],
    })).put(`/admin/insights/${props.insight.id}`);
};
</script>

<template>
    <Head title="Edit Insight" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Edit Insight</h1>
                <p class="text-sm text-muted-foreground">Update premium settings and published visibility.</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Insight Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="space-y-2">
                            <Label for="title">Title</Label>
                            <Input id="title" v-model="form.title" />
                            <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" />
                            <p v-if="form.errors.slug" class="text-sm text-destructive">{{ form.errors.slug }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="excerpt">Excerpt</Label>
                            <Textarea id="excerpt" v-model="form.excerpt" rows="3" />
                            <p v-if="form.errors.excerpt" class="text-sm text-destructive">{{ form.errors.excerpt }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="content">Content</Label>
                            <Textarea id="content" v-model="form.content" rows="14" />
                            <p v-if="form.errors.content" class="text-sm text-destructive">{{ form.errors.content }}</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="cover_image_url">Cover Image URL</Label>
                                <Input id="cover_image_url" v-model="form.cover_image_url" />
                            </div>
                            <div class="space-y-2">
                                <Label for="tags_input">Tags (comma-separated)</Label>
                                <Input id="tags_input" v-model="form.tags_input" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="preview_chars">Preview Characters</Label>
                                <Input id="preview_chars" v-model.number="form.preview_chars" type="number" min="120" max="3000" />
                            </div>
                            <div class="space-y-2">
                                <Label for="published_at">Publish Date</Label>
                                <Input id="published_at" v-model="form.published_at" type="datetime-local" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.is_premium" type="checkbox" class="h-4 w-4" />
                                Premium Insight
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.is_published" type="checkbox" class="h-4 w-4" />
                                Published
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" :disabled="form.processing">Update Insight</Button>
                            <Link href="/admin/insights">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

