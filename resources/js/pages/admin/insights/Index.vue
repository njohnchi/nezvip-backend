<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Insight {
    id: number;
    title: string;
    slug: string;
    is_premium: boolean;
    is_published: boolean;
    published_at: string | null;
    author?: { name?: string | null };
    created_at: string;
}

interface Props {
    insights: {
        data: Insight[];
        total: number;
    };
    stats: {
        total: number;
        published: number;
        drafts: number;
        premium: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Insights', href: '/admin/insights' },
];

const removeInsight = (id: number) => {
    if (!confirm('Delete this insight?')) return;
    router.delete(`/admin/insights/${id}`);
};

const formatDate = (date: string | null) => (date ? new Date(date).toLocaleDateString() : 'Draft');
</script>

<template>
    <Head title="Insights" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Insights</h1>
                    <p class="text-sm text-muted-foreground">Manage blog and premium insight content.</p>
                </div>
                <Link href="/admin/insights/create">
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        New Insight
                    </Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Total</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.total }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Published</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.published }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Drafts</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.drafts }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Premium</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.premium }}</CardContent>
                </Card>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Access</TableHead>
                                <TableHead>Published</TableHead>
                                <TableHead>Author</TableHead>
                                <TableHead class="w-[120px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="insight in insights.data" :key="insight.id">
                                <TableCell>
                                    <div class="font-medium">{{ insight.title }}</div>
                                    <div class="text-xs text-muted-foreground">/{{ insight.slug }}</div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="insight.is_published ? 'default' : 'secondary'">
                                        {{ insight.is_published ? 'Published' : 'Draft' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="insight.is_premium ? 'outline' : 'secondary'">
                                        {{ insight.is_premium ? 'Premium' : 'Free' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(insight.published_at) }}</TableCell>
                                <TableCell>{{ insight.author?.name ?? 'Unknown' }}</TableCell>
                                <TableCell>
                                    <div class="flex gap-1">
                                        <Link :href="`/admin/insights/${insight.id}/edit`">
                                            <Button variant="ghost" size="icon">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button variant="ghost" size="icon" @click="removeInsight(insight.id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

