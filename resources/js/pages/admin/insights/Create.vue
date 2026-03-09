<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Insights', href: '/admin/insights' },
    { title: 'Create', href: '/admin/insights/create' },
];

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    cover_image_url: '',
    tags_input: '',
    preview_chars: 500,
    is_premium: false,
    is_published: false,
    published_at: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        tags: data.tags_input
            ? data.tags_input.split(',').map((tag) => tag.trim()).filter(Boolean)
            : [],
    })).post('/admin/insights');
};
</script>

<template>
    <Head title="Create Insight" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Create Insight</h1>
                <p class="text-sm text-muted-foreground">Publish free or premium insight content.</p>
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
                            <Label for="slug">Slug (optional)</Label>
                            <Input id="slug" v-model="form.slug" placeholder="leave blank to auto-generate" />
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
                            <p class="text-xs text-muted-foreground">Supports plain text or HTML/markdown from your editor.</p>
                            <p v-if="form.errors.content" class="text-sm text-destructive">{{ form.errors.content }}</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="cover_image_url">Cover Image URL</Label>
                                <Input id="cover_image_url" v-model="form.cover_image_url" placeholder="https://..." />
                                <p v-if="form.errors.cover_image_url" class="text-sm text-destructive">{{ form.errors.cover_image_url }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="tags_input">Tags (comma-separated)</Label>
                                <Input id="tags_input" v-model="form.tags_input" placeholder="ai, strategy, operations" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="preview_chars">Preview Characters</Label>
                                <Input id="preview_chars" v-model.number="form.preview_chars" type="number" min="120" max="3000" />
                                <p v-if="form.errors.preview_chars" class="text-sm text-destructive">{{ form.errors.preview_chars }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="published_at">Publish Date (optional)</Label>
                                <Input id="published_at" v-model="form.published_at" type="datetime-local" />
                                <p v-if="form.errors.published_at" class="text-sm text-destructive">{{ form.errors.published_at }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.is_premium" type="checkbox" class="h-4 w-4" />
                                Premium Insight (locked for non-subscribers)
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.is_published" type="checkbox" class="h-4 w-4" />
                                Publish immediately
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit" :disabled="form.processing">Create Insight</Button>
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

