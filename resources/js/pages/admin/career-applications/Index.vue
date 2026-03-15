<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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

interface CareerRef {
    id: number;
    role_title: string;
    venture_name: string;
}

interface Application {
    id: number;
    full_name: string;
    email: string;
    location: string;
    role_applied_for: string;
    status: string;
    career: CareerRef | null;
    created_at: string;
}

interface Props {
    applications: {
        data: Application[];
        total: number;
        current_page: number;
        last_page: number;
    };
    careers: CareerRef[];
    filters: {
        career_id: string | number;
        status: string;
    };
    stats: {
        total: number;
        pending: number;
        reviewing: number;
        shortlisted: number;
        rejected: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Careers', href: '/admin/careers' },
    { title: 'Applications', href: '/admin/career-applications' },
];

const statusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
    const map: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        pending: 'secondary',
        reviewing: 'outline',
        shortlisted: 'default',
        rejected: 'destructive',
        closed: 'secondary',
    };
    return map[status] ?? 'secondary';
};

const filterByCareer = (value: unknown) => {
    const careerId = String(value ?? 'all');

    router.get('/admin/career-applications', { career_id: careerId === 'all' ? undefined : careerId, status: props.filters.status === 'all' ? undefined : props.filters.status }, { preserveState: true });
};

const filterByStatus = (value: unknown) => {
    const status = String(value ?? 'all');

    router.get('/admin/career-applications', { career_id: props.filters.career_id === 'all' ? undefined : props.filters.career_id, status: status === 'all' ? undefined : status }, { preserveState: true });
};

const removeApplication = (id: number) => {
    if (!confirm('Delete this application?')) return;
    router.delete(`/admin/career-applications/${id}`);
};

const formatDate = (d: string) => new Date(d).toLocaleDateString();
</script>

<template>
    <Head title="Career Applications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Career Applications</h1>
                    <p class="text-sm text-muted-foreground">Review and manage role applications.</p>
                </div>
                <Link href="/admin/careers">
                    <Button variant="outline">Back to Opportunities</Button>
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Total</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.total }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Pending</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.pending }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Shortlisted</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.shortlisted }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2"><CardTitle class="text-sm">Rejected</CardTitle></CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.rejected }}</CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex flex-wrap gap-3">
                        <Select :model-value="String(filters.career_id)" @update:model-value="filterByCareer">
                            <SelectTrigger class="w-[220px]">
                                <SelectValue placeholder="All Opportunities" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Opportunities</SelectItem>
                                <SelectItem v-for="c in careers" :key="c.id" :value="String(c.id)">
                                    {{ c.role_title }} — {{ c.venture_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select :model-value="filters.status" @update:model-value="filterByStatus">
                            <SelectTrigger class="w-[160px]">
                                <SelectValue placeholder="All Statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Statuses</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="reviewing">Reviewing</SelectItem>
                                <SelectItem value="shortlisted">Shortlisted</SelectItem>
                                <SelectItem value="rejected">Rejected</SelectItem>
                                <SelectItem value="closed">Closed</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Applicant</TableHead>
                                <TableHead>Role Applied For</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Received</TableHead>
                                <TableHead class="w-[100px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="app in applications.data" :key="app.id">
                                <TableCell>
                                    <div class="font-medium">{{ app.full_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ app.email }}</div>
                                </TableCell>
                                <TableCell>{{ app.role_applied_for }}</TableCell>
                                <TableCell>{{ app.location }}</TableCell>
                                <TableCell>
                                    <Badge :variant="statusVariant(app.status)">{{ app.status }}</Badge>
                                </TableCell>
                                <TableCell>{{ formatDate(app.created_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex gap-1">
                                        <Link :href="`/admin/career-applications/${app.id}`">
                                            <Button variant="ghost" size="icon">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button variant="ghost" size="icon" @click="removeApplication(app.id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="applications.data.length === 0">
                                <TableCell colspan="6" class="py-10 text-center text-muted-foreground">
                                    No applications found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

