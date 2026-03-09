<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CheckCircle, Clock, Eye, TrendingUp, XCircle } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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

interface Reviewer {
    id: number;
    name: string;
}

interface Submission {
    id: number;
    form_type: string;
    status: string;
    data: Record<string, any>;
    created_at: string;
    reviewed_at: string | null;
    reviewer: Reviewer | null;
}

interface Stats {
    total: number;
    pending: number;
    reviewing: number;
    contacted: number;
    closed: number;
}

interface Props {
    submissions: {
        data: Submission[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    formTypes: string[];
    filters: {
        form_type: string;
        status: string;
    };
    stats: Stats;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Submissions',
        href: '/admin/submissions',
    },
];

const getStatusColor = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
    const colors: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        pending: 'secondary',
        reviewing: 'default',
        contacted: 'default',
        rejected: 'destructive',
        closed: 'secondary',
    };
    return colors[status] || 'secondary';
};

const getFormTypeLabel = (formType: string) => {
    const labels: Record<string, string> = {
        scope_review: 'Scope Review',
        institutional_brief: 'Institutional Brief',
        licensing_review: 'Licensing Review',
        investor_brief: 'Investor Brief',
        production_partner: 'Production Partner',
        distribution_partner: 'Distribution Partner',
        insights_subscribe: 'Newsletter',
        insights_request_report: 'Report Request',
        media_inquiry: 'Media Inquiry',
        career_application: 'Career Application',
    };
    return labels[formType] || formType;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const filterByType = (value: any) => {
    const type = String(value || 'all');
    router.get('/admin/submissions', { form_type: type, status: props.filters.status }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByStatus = (value: any) => {
    const status = String(value || 'all');
    router.get('/admin/submissions', { form_type: props.filters.form_type, status }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getSubmitterName = (submission: Submission) => {
    return submission.data.full_name || submission.data.email || 'N/A';
};

const getSubmitterEmail = (submission: Submission) => {
    return submission.data.email || 'N/A';
};
</script>

<template>
    <Head title="Form Submissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Form Submissions</h1>
                <p class="text-sm text-muted-foreground">
                    Review and manage form submissions from your website
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
                        <CardTitle class="text-sm font-medium">Contacted</CardTitle>
                        <CheckCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.contacted }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Closed</CardTitle>
                        <XCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.closed }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle>Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4">
                        <div class="w-64">
                            <Select :model-value="filters.form_type" @update:model-value="filterByType">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Form Types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Form Types</SelectItem>
                                    <SelectItem
                                        v-for="type in formTypes"
                                        :key="type"
                                        :value="type"
                                    >
                                        {{ getFormTypeLabel(type) }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="w-48">
                            <Select :model-value="filters.status" @update:model-value="filterByStatus">
                                <SelectTrigger>
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Statuses</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="reviewing">Reviewing</SelectItem>
                                    <SelectItem value="contacted">Contacted</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                    <SelectItem value="closed">Closed</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Submissions Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Submissions</CardTitle>
                    <CardDescription>
                        Showing {{ submissions.data.length }} of {{ submissions.total }} submissions
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Form Type</TableHead>
                                <TableHead>Submitter</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Submitted</TableHead>
                                <TableHead class="w-20">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="submission in submissions.data"
                                :key="submission.id"
                            >
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ getFormTypeLabel(submission.form_type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="flex flex-col">
                                        <span class="text-sm">{{ getSubmitterName(submission) }}</span>
                                        <span class="text-xs text-muted-foreground">
                                            {{ getSubmitterEmail(submission) }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusColor(submission.status)">
                                        {{ submission.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ formatDate(submission.created_at) }}
                                </TableCell>
                                <TableCell>
                                    <Link :href="`/admin/submissions/${submission.id}`">
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
                v-if="submissions.total > submissions.per_page"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ (submissions.current_page - 1) * submissions.per_page + 1 }} to
                    {{ Math.min(submissions.current_page * submissions.per_page, submissions.total) }}
                    of {{ submissions.total }} submissions
                </p>
            </div>
        </div>
    </AppLayout>
</template>




