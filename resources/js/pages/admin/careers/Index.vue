<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Briefcase, Pencil, Plus, Trash2, Users } from 'lucide-vue-next';
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

interface Career {
    id: number;
    venture_name: string;
    role_title: string;
    location: string;
    engagement_type: string;
    is_active: boolean;
    applications_count: number;
    created_at: string;
}

interface Props {
    careers: {
        data: Career[];
        total: number;
    };
    stats: {
        total: number;
        active: number;
        inactive: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Careers', href: '/admin/careers' },
];

const removeCareer = (id: number) => {
    if (!confirm('Delete this career opportunity?')) return;
    router.delete(`/admin/careers/${id}`);
};

const formatDate = (date: string) => new Date(date).toLocaleDateString();
</script>

<template>
    <Head title="Careers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Career Opportunities</h1>
                    <p class="text-sm text-muted-foreground">Manage venture opportunities and role postings.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/admin/career-applications">
                        <Button variant="outline">
                            <Users class="mr-2 h-4 w-4" />
                            Applications
                        </Button>
                    </Link>
                    <Link href="/admin/careers/create">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            New Opportunity
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm">Total</CardTitle>
                    </CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.total }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm">Active</CardTitle>
                    </CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.active }}</CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm">Inactive</CardTitle>
                    </CardHeader>
                    <CardContent class="text-2xl font-semibold">{{ stats.inactive }}</CardContent>
                </Card>
            </div>

            <Card>
                <CardContent class="pt-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Role</TableHead>
                                <TableHead>Venture</TableHead>
                                <TableHead>Location</TableHead>
                                <TableHead>Type</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>Applications</TableHead>
                                <TableHead>Posted</TableHead>
                                <TableHead class="w-[100px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="career in careers.data" :key="career.id">
                                <TableCell class="font-medium">{{ career.role_title }}</TableCell>
                                <TableCell>{{ career.venture_name }}</TableCell>
                                <TableCell>{{ career.location }}</TableCell>
                                <TableCell>{{ career.engagement_type }}</TableCell>
                                <TableCell>
                                    <Badge :variant="career.is_active ? 'default' : 'secondary'">
                                        {{ career.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Link
                                        :href="`/admin/career-applications?career_id=${career.id}`"
                                        class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                                    >
                                        <Briefcase class="h-3 w-3" />
                                        {{ career.applications_count }}
                                    </Link>
                                </TableCell>
                                <TableCell>{{ formatDate(career.created_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex gap-1">
                                        <Link :href="`/admin/careers/${career.id}/edit`">
                                            <Button variant="ghost" size="icon">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button variant="ghost" size="icon" @click="removeCareer(career.id)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="careers.data.length === 0">
                                <TableCell colspan="8" class="py-10 text-center text-muted-foreground">
                                    No career opportunities yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

