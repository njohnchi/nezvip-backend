<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { AlertCircle, ArrowLeft, Copy, Trash2 } from 'lucide-vue-next';
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
interface RelatedRecord {
    kind: 'submission' | 'diagnostic'; id: number; label: string;
    reference: string; status: string; created_at: string | null;
}
interface Contradiction {
    field: string; previous: string; previous_reference: string; previous_at: string | null;
    current: string; current_reference: string; current_at: string | null;
}
interface Submission {
    id: number; form_type: string; status: string; data: Record<string, any>;
    case_reference: string | null; operator_assisted: boolean;
    admin_notes: string | null; created_at: string; reviewed_at: string | null; reviewer: Reviewer | null;
}
interface Props { submission: Submission; related: RelatedRecord[]; contradictions: Contradiction[]; }
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
const relatedHref = (record: RelatedRecord) => record.kind === 'submission' ? `/admin/submissions/${record.id}` : `/admin/diagnostics/${record.id}`;
const getFormTypeLabel = (t: string) => {
    const labels: Record<string, string> = {
        scope_review: 'Scope Review', institutional_brief: 'Institutional Brief',
        licensing_review: 'Licensing Review', investor_brief: 'Investor Brief',
        production_partner: 'Production Partner', distribution_partner: 'Distribution Partner',
        insights_subscribe: 'Newsletter', insights_request_report: 'Report Request',
        media_inquiry: 'Media Inquiry', career_application: 'Career Application',
        venture_deconstruction: 'Venture Deconstruction', venture_orientation: 'Strategic Venture Orientation',
        venture_synthesis: 'Venture Synthesis', venture_architecture_request: 'Venture Architecture Request',
        program_brief_request: 'Program Brief Request', retainer_request: 'Retainer Request',
    };
    return labels[t] || t;
};
const referencePrefixes: Record<string, string> = {
    scope_review: 'SR', institutional_brief: 'IB', licensing_review: 'LR', investor_brief: 'INV',
    production_partner: 'PP', distribution_partner: 'DP', insights_subscribe: 'IS',
    insights_request_report: 'IRR', media_inquiry: 'MI', career_application: 'CA',
    venture_deconstruction: 'VDS', venture_orientation: 'VO', venture_synthesis: 'SYN',
    venture_architecture_request: 'VA', program_brief_request: 'PBR', retainer_request: 'RTR',
};
const getReference = (s: Submission) => `${referencePrefixes[s.form_type] || 'FS'}-${String(s.id).padStart(6, '0')}`;
const renderValue = (v: any): string => Array.isArray(v) ? v.join(', ') : typeof v === 'object' && v !== null ? JSON.stringify(v, null, 2) : String(v);
const formatFieldName = (k: string) => k.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

const copied = ref(false);

const getValue = (v: any, missing = 'NOT PROVIDED'): string => {
    if (v === null || v === undefined) return missing;
    if (typeof v === 'string' && v.trim() === '') return missing;
    if (Array.isArray(v)) return v.length === 0 ? missing : v.join(', ');
    return String(v);
};

const formatSubmissionDateTime = (d: string) => {
    return new Date(d).toLocaleString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
        timeZoneName: 'short'
    });
};

const generateSubmissionDataText = (): string => {
    const s = props.submission;
    const data = s.data || {};
    const lines: string[] = [];

    lines.push('SUBMISSION DATA — VENTURE ARCHITECTURE');
    lines.push(`Submission Reference: ${getReference(s)}`);
    lines.push(`Case Reference: ${getValue(s.case_reference, 'NOT PROVIDED')}`);
    lines.push(`Submitted At: ${formatSubmissionDateTime(s.created_at)}`);
    lines.push('Service: Venture Architecture Request');
    lines.push(`Request Status: ${getValue(data.request_status, 'UNKNOWN')}`);
    lines.push(`Full Name: ${getValue(data.full_name, 'NOT PROVIDED')}`);
    lines.push(`Email: ${getValue(data.email, 'NOT PROVIDED')}`);
    lines.push(`Organization: ${getValue(data.organization, 'NOT PROVIDED')}`);
    lines.push(`Undertaking / Venture / Project Name: ${getValue(data.undertaking_name, 'NOT PROVIDED')}`);
    const caseLinkage = data.case_linkage_status === 'UNCONFIRMED' ? 'UNCONFIRMED' : (s.case_reference ? 'VERIFIED' : 'UNKNOWN');
    lines.push(`Case Linkage Status: ${caseLinkage}`);
    lines.push(`Architecture Entry Path: ${getValue(data.architecture_entry_path, 'UNKNOWN')}`);
    lines.push(`Architecture Request Type: ${getValue(data.architecture_request_type, 'UNKNOWN')}`);
    lines.push(`Undertaking Context: ${getValue(data.undertaking_context, 'NOT PROVIDED')}`);
    lines.push(`Requested Design Work / Scope: ${getValue(data.scope_description, 'NOT PROVIDED')}`);
    lines.push(`Expected Architecture Outcome: ${getValue(data.expected_architecture_outcome, 'NOT PROVIDED')}`);
    lines.push(`Known Constraints / Requirements: ${getValue(data.known_constraints_requirements, 'NOT PROVIDED')}`);
    lines.push(`Notes: ${getValue(data.notes, 'NOT PROVIDED')}`);

    const supportingDocs = data.supporting_documents_links;
    const supportingDocsText = supportingDocs && (Array.isArray(supportingDocs) ? supportingDocs.length > 0 : String(supportingDocs).trim() !== '')
        ? (Array.isArray(supportingDocs) ? supportingDocs.join(', ') : String(supportingDocs))
        : 'NONE';
    lines.push(`Supporting Document / Link References: ${supportingDocsText}`);

    const relatedRefs = props.related.map(r => r.reference).filter(Boolean);
    lines.push(`Related Case / Prior Submission References: ${relatedRefs.length > 0 ? relatedRefs.join(', ') : 'NONE'}`);

    const authoritySubmitted = data.authority_to_submit;
    const authorityAt = data.authority_to_submit_at;
    let authorityText: string;
    if (authoritySubmitted === true) {
        authorityText = `Yes${authorityAt ? ` (${formatSubmissionDateTime(authorityAt)})` : ''}`;
    } else if (authoritySubmitted === false) {
        authorityText = 'No';
    } else {
        authorityText = 'NOT PROVIDED';
    }
    lines.push(`Authority to Submit Declaration: ${authorityText}`);

    const engagementAuth = data.engagement_authorization;
    lines.push(`Engagement Authorization: ${engagementAuth === 'NOT_VERIFIED' ? 'NOT VERIFIED' : getValue(engagementAuth, 'NOT VERIFIED')}`);

    const diagnosticAuth = data.diagnostic_authorization;
    const diagnosticRef = data.diagnostic_reference;
    let diagnosticText: string;
    if (diagnosticAuth === 'NOT_APPLICABLE') {
        diagnosticText = 'NOT APPLICABLE';
    } else if (diagnosticAuth === 'VERIFIED' && diagnosticRef) {
        diagnosticText = `VERIFIED (ref: ${diagnosticRef})`;
    } else {
        diagnosticText = getValue(diagnosticAuth, 'NOT APPLICABLE');
    }
    lines.push(`Diagnostic Architecture Authorization: ${diagnosticText}`);

    const correctionRefs: string[] = [];
    props.contradictions.forEach(c => {
        if (c.previous_reference) correctionRefs.push(c.previous_reference);
        if (c.current_reference) correctionRefs.push(c.current_reference);
    });
    lines.push(`Clarification / Correction References: ${correctionRefs.length > 0 ? correctionRefs.join(', ') : 'NONE'}`);

    const deliveryStatus = data.processing_delivery_status;
    lines.push(`Document / Delivery Status: ${getValue(deliveryStatus, 'NONE')}`);

    return lines.join('\n');
};

const copySubmissionData = async () => {
    const text = generateSubmissionDataText();
    try {
        await navigator.clipboard.writeText(text);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            copied.value = true;
            setTimeout(() => { copied.value = false; }, 2000);
        } catch {
            alert('Failed to copy submission data.');
        } finally {
            document.body.removeChild(textarea);
        }
    }
};
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
                    <Badge v-if="submission.operator_assisted" variant="outline">Operator-assisted</Badge>
                    <Button variant="outline" @click="copySubmissionData">
                        <Copy class="h-4 w-4 mr-2" />
                        {{ copied ? 'Copied!' : 'Copy Submission Data' }}
                    </Button>
                    <Button variant="destructive" size="icon" @click="deleteSubmission"><Trash2 class="h-4 w-4" /></Button>
                </div>
            </div>
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader><CardTitle>Submission Data</CardTitle><CardDescription>All information provided</CardDescription></CardHeader>
                        <CardContent>
                            <div class="mb-4 grid gap-2 rounded-md border border-border bg-muted/40 p-4 text-sm sm:grid-cols-2">
                                <div>
                                    <Label class="text-muted-foreground">Reference</Label>
                                    <p class="font-medium">{{ getReference(submission) }}</p>
                                </div>
                                <div>
                                    <Label class="text-muted-foreground">Case Reference</Label>
                                    <p class="font-medium">{{ submission.case_reference || '—' }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div v-for="(value, key) in submission.data" :key="key" class="border-b pb-4 last:border-0">
                                    <Label class="text-muted-foreground">{{ formatFieldName(String(key)) }}</Label>
                                    <p class="mt-1 whitespace-pre-wrap">{{ renderValue(value) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card v-if="contradictions.length" class="mt-6 border-amber-300 bg-amber-50/60">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-amber-900">
                                <AlertCircle class="h-4 w-4" />
                                Needs clarification
                            </CardTitle>
                            <CardDescription>These fields changed since an earlier submission. Confirm which value is correct.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-3">
                                <li v-for="item in contradictions" :key="item.field" class="border-b border-amber-200 pb-3 last:border-0 last:pb-0">
                                    <p class="font-medium">{{ formatFieldName(item.field) }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        <span class="line-through">{{ item.previous }}</span>
                                        <span class="mx-1">→</span>
                                        <span class="font-medium text-foreground">{{ item.current }}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">{{ item.previous_reference }} → {{ item.current_reference }}</p>
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                    <Card v-if="related.length" class="mt-6">
                        <CardHeader>
                            <CardTitle>Related Records</CardTitle>
                            <CardDescription>Other submissions sharing this case reference</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul class="space-y-3">
                                <li v-for="record in related" :key="`${record.kind}-${record.id}`" class="flex items-center justify-between gap-4 border-b pb-3 last:border-0 last:pb-0">
                                    <Link :href="relatedHref(record)" class="group">
                                        <p class="font-medium group-hover:text-primary group-hover:underline">{{ record.label }}</p>
                                        <p class="text-sm text-muted-foreground">{{ record.reference }} · {{ formatDate(record.created_at || '') }}</p>
                                    </Link>
                                    <Badge variant="secondary">{{ record.status }}</Badge>
                                </li>
                            </ul>
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
