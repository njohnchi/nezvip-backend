<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil } from 'lucide-vue-next';
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
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface EmailTemplate {
    id: number;
    key: string;
    name: string;
    description: string | null;
    subject: string;
    is_active: boolean;
    available_variables: string[];
    updated_at: string;
}

interface Props {
    templates: EmailTemplate[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Email Templates',
        href: '/admin/email-templates',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Email Templates" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Email Templates</h1>
                <p class="text-sm text-muted-foreground">
                    Manage acknowledgement email subjects, content, and activation status.
                </p>
            </div>

            <div class="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Template Key</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Variables</TableHead>
                            <TableHead>Last Updated</TableHead>
                            <TableHead class="w-24">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="template in templates" :key="template.id">
                            <TableCell>
                                <div class="font-medium">{{ template.name }}</div>
                                <div class="text-xs text-muted-foreground">{{ template.description }}</div>
                            </TableCell>
                            <TableCell>
                                <code class="rounded bg-muted px-2 py-1 text-xs">{{ template.key }}</code>
                            </TableCell>
                            <TableCell>
                                <Badge :variant="template.is_active ? 'default' : 'secondary'">
                                    {{ template.is_active ? 'Active' : 'Inactive' }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="variable in template.available_variables"
                                        :key="`${template.id}-${variable}`"
                                        variant="outline"
                                    >
                                        {{ variable }}
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell>{{ formatDate(template.updated_at) }}</TableCell>
                            <TableCell>
                                <Link :href="`/admin/email-templates/${template.id}/edit`">
                                    <Button variant="ghost" size="icon">
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                </Link>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="templates.length === 0">
                            <TableCell colspan="6" class="py-8 text-center text-muted-foreground">
                                No email templates found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>

