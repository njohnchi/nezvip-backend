<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Eye, TrendingUp, Clock, CheckCircle, XCircle } from 'lucide-vue-next';

interface Reviewer {
    id: number;
    name: string;
}

interface Diagnostic {
    id: number;
    venture_name: string;
    full_name: string;
    email: string;
    status: string;
    intake_type: string;
    viability_score: number | null;
    created_at: string;
    reviewed_at: string | null;
    reviewer: Reviewer | null;
}

interface Stats {
    total: number;
    pending: number;
    reviewing: number;
    approved: number;
    rejected: number;
}

interface Props {
    diagnostics: {
        data: Diagnostic[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: Stats;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Diagnostics',
        href: '/admin/diagnostics',
    },
];

const getStatusColor = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
    const colors: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        pending: 'secondary',
        reviewing: 'default',
        approved: 'default',
        rejected: 'destructive',
        're-architect': 'outline',
        kill: 'destructive',
    };
    return colors[status] || 'secondary';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Venture Diagnostics" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Venture Diagnostics</h1>
                <p class="text-sm text-muted-foreground">
                    Review and assess venture diagnostic submissions
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Pending</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.pending }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Reviewing</CardTitle>
                        <Eye class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.reviewing }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Approved</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.approved }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Rejected</CardTitle>
                        <XCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.rejected }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Diagnostics Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Submissions</CardTitle>
                    <CardDescription>
                        Showing {{ diagnostics.data.length }} of {{ diagnostics.total }} diagnostics
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Venture</TableHead>
                                <TableHead>Submitter</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Score</TableHead>
                                <TableHead>Submitted</TableHead>
                                <TableHead class="w-20">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="diagnostic in diagnostics.data"
                                :key="diagnostic.id"
                            >
                                <TableCell class="font-medium">
                                    {{ diagnostic.venture_name }}
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="text-sm">{{ diagnostic.full_name }}</span>
                                        <span class="text-xs text-muted-foreground">{{ diagnostic.email }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ diagnostic.intake_type }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusColor(diagnostic.status)">
                                        {{ diagnostic.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span v-if="diagnostic.viability_score" class="font-medium">
                                        {{ diagnostic.viability_score }}/100
                                    </span>
                                    <span v-else class="text-muted-foreground">—</span>
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(diagnostic.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <Link :href="`/admin/diagnostics/${diagnostic.id}`">
                                        <Button variant="ghost" size="sm">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div
                v-if="diagnostics.total > diagnostics.per_page"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ (diagnostics.current_page - 1) * diagnostics.per_page + 1 }} to
                    {{ Math.min(diagnostics.current_page * diagnostics.per_page, diagnostics.total) }}
                    of {{ diagnostics.total }} diagnostics
                </p>
            </div>
        </div>
    </AppLayout>
</template>

