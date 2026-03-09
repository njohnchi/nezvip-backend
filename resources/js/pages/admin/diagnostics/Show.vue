<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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

interface Reviewer {
    id: number;
    name: string;
}

interface Diagnostic {
    id: number;
    // Intake
    intake_type: string;
    status: string;
    full_name: string;
    email: string;
    location: string;
    organization_name: string | null;
    organization_type: string | null;
    role_title: string | null;
    // Section A
    venture_name: string;
    industry_category: string;
    venture_description: string;
    // Section B
    what_is_sold: string;
    primary_customer_type: string;
    core_value_proposition: string;
    // Section C
    revenue_model: string;
    pricing_logic: string;
    payment_flow: string;
    revenue_risk_carrier: string;
    // Section D
    delivery_process: string;
    who_performs_work: string;
    operational_dependencies: string;
    scale_bottleneck: string;
    // Section E
    claimed_bottlenecks: string;
    assets_ip_licenses: string | null;
    competitive_positioning: string;
    // Section F
    growth_mechanism: string;
    founder_role: string;
    replaceability_assumptions: string;
    // Section G
    customer_awareness: string;
    demand_origin: string;
    demand_responsibility: string;
    demand_ownership: string;
    underperformance_impact: string;
    new_seller_benefits: string;
    demand_dynamics: string;
    // Section H
    capital_intensity: string;
    regulatory_constraints: string;
    known_risks: string;
    // Section I
    additional_context: string | null;
    known_unknowns: string | null;
    declared_exclusions: string;
    // Budget & Timeline
    budget_range: string;
    timeline: string;
    proof_links: string[];
    // Assessment
    admin_notes: string | null;
    viability_score: number | null;
    recommended_action: string | null;
    reviewed_at: string | null;
    reviewer: Reviewer | null;
    created_at: string;
}

interface Props {
    diagnostic: Diagnostic;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Diagnostics',
        href: '/admin/diagnostics',
    },
    {
        title: 'Review',
        href: `/admin/diagnostics/${props.diagnostic.id}`,
    },
];

const form = useForm({
    status: props.diagnostic.status,
    admin_notes: props.diagnostic.admin_notes || '',
    viability_score: props.diagnostic.viability_score,
    recommended_action: props.diagnostic.recommended_action || '',
});

const handleSubmit = () => {
    form.put(`/admin/diagnostics/${props.diagnostic.id}`, {
        preserveScroll: true,
    });
};

const deleteDiagnostic = () => {
    if (confirm('Are you sure you want to delete this diagnostic? This action cannot be undone.')) {
        router.delete(`/admin/diagnostics/${props.diagnostic.id}`);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Diagnostic: ${diagnostic.venture_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="router.visit('/admin/diagnostics')"
                    >
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            {{ diagnostic.venture_name }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Submitted {{ formatDate(diagnostic.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Badge>{{ diagnostic.intake_type }}</Badge>
                    <Badge :variant="diagnostic.status === 'approved' ? 'default' : 'secondary'">
                        {{ diagnostic.status }}
                    </Badge>
                    <Button variant="destructive" size="icon" @click="deleteDiagnostic">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Submitter Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Submitter Information</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-muted-foreground">Name</Label>
                                <p class="font-medium">{{ diagnostic.full_name }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Email</Label>
                                <p class="font-medium">{{ diagnostic.email }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Location</Label>
                                <p class="font-medium">{{ diagnostic.location }}</p>
                            </div>
                            <div v-if="diagnostic.organization_name">
                                <Label class="text-muted-foreground">Organization</Label>
                                <p class="font-medium">{{ diagnostic.organization_name }}</p>
                            </div>
                            <div v-if="diagnostic.role_title">
                                <Label class="text-muted-foreground">Role</Label>
                                <p class="font-medium">{{ diagnostic.role_title }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section A: Venture Identity -->
                    <Card>
                        <CardHeader>
                            <CardTitle>A. Venture Identity</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">Industry Category</Label>
                                <p>{{ diagnostic.industry_category }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Description</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.venture_description }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section B: Offering & Value -->
                    <Card>
                        <CardHeader>
                            <CardTitle>B. Offering & Value</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">What is Sold</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.what_is_sold }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Primary Customer Type</Label>
                                <p>{{ diagnostic.primary_customer_type }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Core Value Proposition</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.core_value_proposition }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section C: Business Model -->
                    <Card>
                        <CardHeader>
                            <CardTitle>C. Business Model</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">Revenue Model</Label>
                                <p>{{ diagnostic.revenue_model }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Pricing Logic</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.pricing_logic }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Payment Flow</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.payment_flow }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Revenue Risk Carrier</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.revenue_risk_carrier }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section D: Operations -->
                    <Card>
                        <CardHeader>
                            <CardTitle>D. Operations</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">Delivery Process</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.delivery_process }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Who Performs Work</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.who_performs_work }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Operational Dependencies</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.operational_dependencies }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Scale Bottleneck</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.scale_bottleneck }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section E: Control & Advantage -->
                    <Card>
                        <CardHeader>
                            <CardTitle>E. Control & Advantage</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">Claimed Bottlenecks</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.claimed_bottlenecks }}</p>
                            </div>
                            <div v-if="diagnostic.assets_ip_licenses">
                                <Label class="text-muted-foreground">Assets/IP/Licenses</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.assets_ip_licenses }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Competitive Positioning</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.competitive_positioning }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section F: Growth & Scale -->
                    <Card>
                        <CardHeader>
                            <CardTitle>F. Growth & Scale</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">Growth Mechanism</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.growth_mechanism }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Founder Role</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.founder_role }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Replaceability Assumptions</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.replaceability_assumptions }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section G: Demand Context -->
                    <Card>
                        <CardHeader>
                            <CardTitle>G. Demand Context</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">Customer Awareness</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.customer_awareness }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Demand Origin</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.demand_origin }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Demand Responsibility</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.demand_responsibility }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Demand Ownership</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.demand_ownership }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Underperformance Impact</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.underperformance_impact }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">New Seller Benefits</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.new_seller_benefits }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Demand Dynamics</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.demand_dynamics }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section H: Risk & Constraints -->
                    <Card>
                        <CardHeader>
                            <CardTitle>H. Risk & Constraints</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-muted-foreground">Capital Intensity</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.capital_intensity }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Regulatory Constraints</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.regulatory_constraints }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Known Risks</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.known_risks }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Section I: Notes -->
                    <Card>
                        <CardHeader>
                            <CardTitle>I. Notes & Clarifications</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="diagnostic.additional_context">
                                <Label class="text-muted-foreground">Additional Context</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.additional_context }}</p>
                            </div>
                            <div v-if="diagnostic.known_unknowns">
                                <Label class="text-muted-foreground">Known Unknowns</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.known_unknowns }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Declared Exclusions</Label>
                                <p class="whitespace-pre-wrap">{{ diagnostic.declared_exclusions }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Budget & Timeline -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Budget & Timeline</CardTitle>
                        </CardHeader>
                        <CardContent class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label class="text-muted-foreground">Budget Range</Label>
                                <p class="font-medium">{{ diagnostic.budget_range }}</p>
                            </div>
                            <div>
                                <Label class="text-muted-foreground">Timeline</Label>
                                <p class="font-medium">{{ diagnostic.timeline }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <Label class="text-muted-foreground">Proof Links</Label>
                                <ul class="mt-2 space-y-1">
                                    <li v-for="(link, index) in diagnostic.proof_links" :key="index">
                                        <a :href="link" target="_blank" class="text-sm text-primary hover:underline">
                                            {{ link }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Assessment Panel -->
                <div class="lg:col-span-1">
                    <Card class="sticky top-6">
                        <CardHeader>
                            <CardTitle>Assessment</CardTitle>
                            <CardDescription>Review and update diagnostic status</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="handleSubmit" class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="status">Status</Label>
                                    <Select v-model="form.status" required>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pending">Pending</SelectItem>
                                            <SelectItem value="reviewing">Reviewing</SelectItem>
                                            <SelectItem value="approved">Approved</SelectItem>
                                            <SelectItem value="rejected">Rejected</SelectItem>
                                            <SelectItem value="re-architect">Re-architect</SelectItem>
                                            <SelectItem value="kill">Kill</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.status" class="text-sm text-destructive">
                                        {{ form.errors.status }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="viability_score">Viability Score (0-100)</Label>
                                    <Input
                                        id="viability_score"
                                        v-model.number="form.viability_score"
                                        type="number"
                                        min="0"
                                        max="100"
                                        placeholder="Enter score"
                                    />
                                    <p v-if="form.errors.viability_score" class="text-sm text-destructive">
                                        {{ form.errors.viability_score }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="recommended_action">Recommended Action</Label>
                                    <Textarea
                                        id="recommended_action"
                                        v-model="form.recommended_action"
                                        placeholder="Enter recommended next steps..."
                                        rows="4"
                                    />
                                    <p v-if="form.errors.recommended_action" class="text-sm text-destructive">
                                        {{ form.errors.recommended_action }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="admin_notes">Admin Notes</Label>
                                    <Textarea
                                        id="admin_notes"
                                        v-model="form.admin_notes"
                                        placeholder="Internal notes..."
                                        rows="6"
                                    />
                                    <p v-if="form.errors.admin_notes" class="text-sm text-destructive">
                                        {{ form.errors.admin_notes }}
                                    </p>
                                </div>

                                <Separator />

                                <div v-if="diagnostic.reviewed_at" class="text-sm text-muted-foreground">
                                    <p>Last reviewed: {{ formatDate(diagnostic.reviewed_at) }}</p>
                                    <p v-if="diagnostic.reviewer">By: {{ diagnostic.reviewer.name }}</p>
                                </div>

                                <Button type="submit" class="w-full" :disabled="form.processing">
                                    Update Assessment
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

