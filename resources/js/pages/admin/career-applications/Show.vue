<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface CareerRef {
    id: number;
    role_title: string;
    venture_name: string;
    location: string;
    engagement_type: string;
}

interface Reviewer {
    id: number;
    name: string;
}

interface Application {
    id: number;
    full_name: string;
    email: string;
    location: string;
    role_applied_for: string;
    relevant_experience: string;
    profile_link: string;
    status: string;
    admin_notes: string | null;
    career: CareerRef | null;
    reviewer: Reviewer | null;
    created_at: string;
    reviewed_at: string | null;
}

interface Props {
    application: Application;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Applications', href: '/admin/career-applications' },
    { title: props.application.full_name, href: `/admin/career-applications/${props.application.id}` },
];

const form = useForm({
    status: props.application.status,
    admin_notes: props.application.admin_notes ?? '',
});

const handleSubmit = () => {
    form.put(`/admin/career-applications/${props.application.id}`, { preserveScroll: true });
};

const deleteApplication = () => {
    if (confirm('Delete this application permanently?')) {
        router.delete(`/admin/career-applications/${props.application.id}`);
    }
};

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

const formatDate = (d: string) =>
    new Date(d).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
</script>

<template>
    <Head :title="`Application: ${application.full_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" @click="router.visit('/admin/career-applications')">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ application.full_name }}</h1>
                        <p class="text-sm text-muted-foreground">Applied {{ formatDate(application.created_at) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge :variant="statusVariant(application.status)">{{ application.status }}</Badge>
                    <Button variant="destructive" size="icon" @click="deleteApplication">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Application Details -->
                <div class="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Applicant Details</CardTitle>
                            <CardDescription>{{ application.role_applied_for }}</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label class="text-muted-foreground">Full Name</Label>
                                    <p class="mt-1 font-medium">{{ application.full_name }}</p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Email</Label>
                                    <p class="mt-1">
                                        <a :href="`mailto:${application.email}`" class="text-primary hover:underline">
                                            {{ application.email }}
                                        </a>
                                    </p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Location</Label>
                                    <p class="mt-1">{{ application.location }}</p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Profile / CV Link</Label>
                                    <p class="mt-1">
                                        <a
                                            :href="application.profile_link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 text-primary hover:underline"
                                        >
                                            View Profile
                                            <ExternalLink class="h-3 w-3" />
                                        </a>
                                    </p>
                                </div>
                            </div>

                            <Separator />

                            <div>
                                <Label class="text-muted-foreground">Relevant Experience</Label>
                                <p class="mt-2 whitespace-pre-wrap text-sm">{{ application.relevant_experience }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="application.career">
                        <CardHeader>
                            <CardTitle>Role Context</CardTitle>
                            <CardDescription>{{ application.career.role_title }} — {{ application.career.venture_name }}</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex flex-wrap gap-3 text-sm text-muted-foreground">
                                <span>📍 {{ application.career.location }}</span>
                                <span>💼 {{ application.career.engagement_type }}</span>
                            </div>
                            <div class="text-right">
                                <Link :href="`/admin/careers/${application.career.id}/edit`">
                                    <Button variant="outline" size="sm">View Opportunity</Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Action Panel -->
                <div class="lg:col-span-1">
                    <Card class="sticky top-6">
                        <CardHeader>
                            <CardTitle>Review Application</CardTitle>
                            <CardDescription>Update status and notes</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form class="space-y-4" @submit.prevent="handleSubmit">
                                <div class="space-y-2">
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger id="status">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="reviewing">Reviewing</SelectItem>
                                            <SelectItem value="shortlisted">Shortlisted</SelectItem>
                                            <SelectItem value="rejected">Rejected</SelectItem>
                                            <SelectItem value="closed">Closed</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label for="admin_notes">Admin Notes</Label>
                                    <Textarea
                                        id="admin_notes"
                                        v-model="form.admin_notes"
                                        placeholder="Internal notes about this applicant..."
                                        rows="7"
                                    />
                                </div>

                                <Separator />

                                <div v-if="application.reviewed_at" class="text-sm text-muted-foreground">
                                    <p>Last reviewed: {{ formatDate(application.reviewed_at) }}</p>
                                    <p v-if="application.reviewer">By: {{ application.reviewer.name }}</p>
                                </div>

                                <Button type="submit" class="w-full" :disabled="form.processing">
                                    Update Application
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

