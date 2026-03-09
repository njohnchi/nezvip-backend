<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
interface Reviewer { id: number; name: string; }
interface Submission {
    id: number; form_type: string; status: string; data: Record<string, any>;
    admin_notes: string | null; created_at: string; reviewed_at: string | null; reviewer: Reviewer | null;
}
interface Props { submission: Submission; }
const props = defineProps<Props>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Submissions', href: '/admin/submissions' },
    { title: 'Review', href: `/admin/submissions/${props.submission.id}` },
];
const form = useForm({ status: props.submission.status, admin_notes: props.submission.admin_notes || '' });
const handleSubmit = () => { form.put(`/admin/submissions/${props.submission.id}`, { preserveScroll: true }); };
const deleteSubmission = () => {
    if (confirm('Are you sure?')) router.delete(`/admin/submissions/${props.submission.id}`);
};
const formatDate = (d: string) => new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
const getFormTypeLabel = (t: string) => {
    const labels: Record<string, string> = {
        scope_review: 'Scope Review', institutional_brief: 'Institutional Brief',
        licensing_review: 'Licensing Review', investor_brief: 'Investor Brief',
        production_partner: 'Production Partner', distribution_partner: 'Distribution Partner',
        insights_subscribe: 'Newsletter', insights_request_report: 'Report Request',
        media_inquiry: 'Media Inquiry', career_application: 'Career Application'
    };
    return labels[t] || t;
};
const renderValue = (v: any): string => Array.isArray(v) ? v.join(', ') : typeof v === 'object' && v !== null ? JSON.stringify(v, null, 2) : String(v);
const formatFieldName = (k: string) => k.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
</script>
<template>
    <Head :title="`Submission: ${getFormTypeLabel(submission.form_type)}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" @click="router.visit('/admin/submissions')"><ArrowLeft class="h-4 w-4" /></Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ getFormTypeLabel(submission.form_type) }}</h1>
                        <p class="text-sm text-muted-foreground">Submitted {{ formatDate(submission.created_at) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge>{{ getFormTypeLabel(submission.form_type) }}</Badge>
                    <Badge :variant="submission.status === 'contacted' ? 'default' : 'secondary'">{{ submission.status }}</Badge>
                    <Button variant="destructive" size="icon" @click="deleteSubmission"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </div>
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader><CardTitle>Submission Data</CardTitle><CardDescription>All information provided</CardDescription></CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="(value, key) in submission.data" :key="key" class="border-b pb-4 last:border-0">
                                    <Label class="text-muted-foreground">{{ formatFieldName(String(key)) }}</Label>
                                    <p class="mt-1 whitespace-pre-wrap">{{ renderValue(value) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                <div class="lg:col-span-1">
                    <Card class="sticky top-6">
                        <CardHeader><CardTitle>Manage Submission</CardTitle><CardDescription>Update status</CardDescription></CardHeader>
                        <CardContent>
                            <form @submit.prevent="handleSubmit" class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status" required>
                                        <SelectTrigger><SelectValue placeholder="Select status" /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="reviewing">Reviewing</SelectItem>
                                            <SelectItem value="contacted">Contacted</SelectItem>
                                            <SelectItem value="rejected">Rejected</SelectItem>
                                            <SelectItem value="closed">Closed</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label for="admin_notes">Admin Notes</Label>
                                    <Textarea id="admin_notes" v-model="form.admin_notes" placeholder="Notes..." rows="8" />
                                </div>
                                <Separator />
                                <div v-if="submission.reviewed_at" class="text-sm text-muted-foreground">
                                    <p>Last reviewed: {{ formatDate(submission.reviewed_at) }}</p>
                                    <p v-if="submission.reviewer">By: {{ submission.reviewer.name }}</p>
                                </div>
                                <Button type="submit" class="w-full" :disabled="form.processing">Update</Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
