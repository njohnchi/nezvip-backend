<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BarChart3, ShieldCheck, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

interface SummaryCard {
    label: string;
    value: number | string;
    description: string;
    href?: string | null;
}

interface PipelineStatus {
    label: string;
    value: number;
}

interface PipelineCard {
    label: string;
    total: number;
    href?: string | null;
    statuses: PipelineStatus[];
}

interface HighlightCard {
    title: string;
    value: number | string;
    description: string;
}

interface TrendSeries {
    label: string;
    values: number[];
    total: number;
}

interface QuickLinkItem {
    label: string;
    description: string;
    href: string;
}

interface FormTypeMetric {
    form_type: string;
    label: string;
    count: number;
}

interface ActivityItem {
    id: string;
    category: string;
    title: string;
    subtitle: string;
    status: string;
    occurred_at: string | null;
    href: string;
}

interface Props {
    permissions: Record<string, boolean>;
    account: {
        roles: string[];
        permissions_count: number;
        email_verified: boolean;
        two_factor_enabled: boolean;
        insights_subscription_active: boolean;
        is_super_admin: boolean;
    };
    summaries: Record<string, SummaryCard>;
    pipelines: Record<string, PipelineCard>;
    highlights: Record<string, HighlightCard>;
    analytics: {
        months: string[];
        series: Record<string, TrendSeries>;
        top_form_types: FormTypeMetric[];
    };
    quick_links: Record<string, QuickLinkItem>;
    recent_activity: ActivityItem[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];

const summaryCards = computed(() => Object.entries(props.summaries) as Array<[string, SummaryCard]>);
const pipelineCards = computed(() => Object.entries(props.pipelines) as Array<[string, PipelineCard]>);
const highlightCards = computed(() => Object.values(props.highlights) as HighlightCard[]);
const analyticsSeries = computed(() => Object.entries(props.analytics.series) as Array<[string, TrendSeries]>);
const quickLinks = computed(() => Object.values(props.quick_links) as QuickLinkItem[]);
const hasAdminAnalytics = computed(() => {
    return summaryCards.value.length > 0
        || pipelineCards.value.length > 0
        || highlightCards.value.length > 0
        || analyticsSeries.value.length > 0
        || props.recent_activity.length > 0;
});

const colorClasses: Record<string, string> = {
    users: 'bg-sky-500',
    insights: 'bg-violet-500',
    applications: 'bg-emerald-500',
    diagnostics: 'bg-amber-500',
    submissions: 'bg-rose-500',
};

const barClass = (key: string) => colorClasses[key] ?? 'bg-primary';

const barHeight = (value: number, maxValue: number) => {
    if (maxValue <= 0) {
        return '8%';
    }

    if (value === 0) {
        return '8%';
    }

    return `${Math.max(Math.round((value / maxValue) * 100), 18)}%`;
};

const maxValue = (series: TrendSeries) => Math.max(...series.values, 0);

const statusVariant = (status: string): 'default' | 'destructive' | 'outline' | 'secondary' => {
    const variants: Record<string, 'default' | 'destructive' | 'outline' | 'secondary'> = {
        published: 'default',
        draft: 'secondary',
        pending: 'secondary',
        reviewing: 'outline',
        contacted: 'default',
        shortlisted: 'default',
        approved: 'default',
        rejected: 'destructive',
        closed: 'secondary',
        kill: 'destructive',
        're-architect': 'outline',
    };

    return variants[status] ?? 'secondary';
};

const formatStatus = (status: string) => status
    .replace(/[-_]/g, ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase());

const formatDate = (value: string | null) => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="max-w-3xl text-sm text-muted-foreground">
                        Operational summaries, publishing signals, and intake analytics for the backend admin team.
                    </p>
                </div>

                <div v-if="quickLinks.length" class="flex flex-wrap gap-2">
                    <Link
                        v-for="link in quickLinks.slice(0, 3)"
                        :key="link.href"
                        :href="link.href"
                    >
                        <Button variant="outline" size="sm">
                            {{ link.label }}
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
                <div class="space-y-6">
                    <div v-if="summaryCards.length" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
                        <Card
                            v-for="[key, summary] in summaryCards"
                            :key="key"
                            class="border-border/60"
                        >
                            <CardHeader class="pb-2">
                                <CardDescription>{{ summary.label }}</CardDescription>
                                <CardTitle class="text-3xl">{{ summary.value }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <p class="text-sm text-muted-foreground">
                                    {{ summary.description }}
                                </p>
                                <Link v-if="summary.href" :href="summary.href">
                                    <Button variant="ghost" size="sm" class="px-0">
                                        View details
                                        <ArrowRight class="ml-2 h-4 w-4" />
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>

                    <div v-if="highlightCards.length" class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-4">
                        <Card
                            v-for="highlight in highlightCards"
                            :key="highlight.title"
                            class="border-border/60 bg-muted/20"
                        >
                            <CardHeader class="pb-2">
                                <CardDescription>{{ highlight.title }}</CardDescription>
                                <CardTitle class="text-2xl">{{ highlight.value }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-sm text-muted-foreground">
                                    {{ highlight.description }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    <div v-if="analyticsSeries.length" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-xl font-semibold">Analytics</h2>
                        </div>

                        <div class="grid gap-4 2xl:grid-cols-2">
                            <Card
                                v-for="[key, series] in analyticsSeries"
                                :key="key"
                                class="border-border/60"
                            >
                                <CardHeader>
                                    <CardDescription>{{ series.label }}</CardDescription>
                                    <CardTitle>{{ series.total }} in the last {{ analytics.months.length }} months</CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-3">
                                    <div class="flex h-32 items-end gap-2">
                                        <div
                                            v-for="(value, index) in series.values"
                                            :key="`${key}-${analytics.months[index]}`"
                                            class="flex flex-1 flex-col items-center justify-end gap-2"
                                        >
                                            <span class="text-xs text-muted-foreground">{{ value }}</span>
                                            <div class="flex h-24 w-full items-end rounded-md bg-muted/50 px-1 py-1">
                                                <div
                                                    class="w-full rounded-sm"
                                                    :class="barClass(key)"
                                                    :style="{ height: barHeight(value, maxValue(series)) }"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-6 gap-2 text-center text-xs text-muted-foreground">
                                        <span v-for="month in analytics.months" :key="month">{{ month }}</span>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <div v-if="pipelineCards.length" class="space-y-4">
                        <div class="flex items-center gap-2">
                            <Sparkles class="h-5 w-5 text-muted-foreground" />
                            <h2 class="text-xl font-semibold">Operational summaries</h2>
                        </div>

                        <div class="grid gap-4 2xl:grid-cols-2">
                            <Card
                                v-for="[key, pipeline] in pipelineCards"
                                :key="key"
                                class="border-border/60"
                            >
                                <CardHeader class="space-y-2">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <CardDescription>{{ pipeline.label }}</CardDescription>
                                            <CardTitle>{{ pipeline.total }} total</CardTitle>
                                        </div>

                                        <Link v-if="pipeline.href" :href="pipeline.href">
                                            <Button variant="ghost" size="sm">Open</Button>
                                        </Link>
                                    </div>
                                </CardHeader>
                                <CardContent class="space-y-3">
                                    <div
                                        v-for="status in pipeline.statuses"
                                        :key="`${pipeline.label}-${status.label}`"
                                        class="space-y-2"
                                    >
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-muted-foreground">{{ status.label }}</span>
                                            <span class="font-medium">{{ status.value }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-muted">
                                            <div
                                                class="h-2 rounded-full bg-primary"
                                                :style="{ width: `${pipeline.total > 0 ? Math.max((status.value / pipeline.total) * 100, status.value > 0 ? 8 : 0) : 0}%` }"
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <div class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_320px]">
                        <Card class="border-border/60">
                            <CardHeader>
                                <CardDescription>Latest items</CardDescription>
                                <CardTitle>Recent activity</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="recent_activity.length" class="space-y-4">
                                    <div
                                        v-for="item in recent_activity"
                                        :key="item.id"
                                        class="flex flex-col gap-3 rounded-lg border border-border/60 p-4 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div class="space-y-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <Badge variant="outline">{{ item.category }}</Badge>
                                                <Badge :variant="statusVariant(item.status)">
                                                    {{ formatStatus(item.status) }}
                                                </Badge>
                                            </div>
                                            <p class="font-medium">{{ item.title }}</p>
                                            <p class="text-sm text-muted-foreground">{{ item.subtitle }}</p>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <span class="text-sm text-muted-foreground">{{ formatDate(item.occurred_at) }}</span>
                                            <Link :href="item.href">
                                                <Button variant="ghost" size="sm">
                                                    Open
                                                    <ArrowRight class="ml-2 h-4 w-4" />
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                    No recent activity is available for the sections you can access yet.
                                </div>
                            </CardContent>
                        </Card>

                        <Card class="border-border/60">
                            <CardHeader>
                                <CardDescription>Most common inbound requests</CardDescription>
                                <CardTitle>Top form types</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="analytics.top_form_types.length" class="space-y-3">
                                    <div
                                        v-for="metric in analytics.top_form_types"
                                        :key="metric.form_type"
                                        class="rounded-lg border border-border/60 p-3"
                                    >
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-sm font-medium">{{ metric.label }}</span>
                                            <Badge variant="secondary">{{ metric.count }}</Badge>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                    Form type analytics will appear after submissions start coming in.
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card v-if="!hasAdminAnalytics" class="border-border/60 border-dashed">
                        <CardHeader>
                            <CardTitle>Nothing to summarize yet</CardTitle>
                            <CardDescription>
                                This account can sign in, but it does not currently have access to any admin analytics modules.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground">
                                Ask a Super Admin to assign the relevant dashboard permissions if you should be managing users,
                                insights, careers, diagnostics, or submissions.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <Card class="border-border/60">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <ShieldCheck class="h-5 w-5 text-muted-foreground" />
                                <CardTitle>Account status</CardTitle>
                            </div>
                            <CardDescription>Your access and security posture at a glance.</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <Badge v-for="role in account.roles" :key="role" variant="secondary">
                                    {{ role }}
                                </Badge>
                                <Badge v-if="!account.roles.length" variant="outline">
                                    No roles assigned
                                </Badge>
                            </div>

                            <Separator />

                            <div class="grid gap-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-muted-foreground">Email verification</span>
                                    <Badge :variant="account.email_verified ? 'default' : 'secondary'">
                                        {{ account.email_verified ? 'Verified' : 'Pending' }}
                                    </Badge>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-muted-foreground">Two-factor authentication</span>
                                    <Badge :variant="account.two_factor_enabled ? 'default' : 'secondary'">
                                        {{ account.two_factor_enabled ? 'Enabled' : 'Disabled' }}
                                    </Badge>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-muted-foreground">Insight subscription</span>
                                    <Badge :variant="account.insights_subscription_active ? 'default' : 'secondary'">
                                        {{ account.insights_subscription_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-muted-foreground">Permission coverage</span>
                                    <span class="font-medium">{{ account.permissions_count }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-muted-foreground">Super Admin</span>
                                    <Badge :variant="account.is_super_admin ? 'default' : 'outline'">
                                        {{ account.is_super_admin ? 'Yes' : 'No' }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="border-border/60">
                        <CardHeader>
                            <CardTitle>Quick links</CardTitle>
                            <CardDescription>Jump into the admin areas you can manage.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="quickLinks.length" class="space-y-3">
                                <Link
                                    v-for="link in quickLinks"
                                    :key="link.href"
                                    :href="link.href"
                                    class="block rounded-lg border border-border/60 p-4 transition-colors hover:bg-muted/40"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-1">
                                            <p class="font-medium">{{ link.label }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                {{ link.description }}
                                            </p>
                                        </div>
                                        <ArrowRight class="mt-0.5 h-4 w-4 text-muted-foreground" />
                                    </div>
                                </Link>
                            </div>

                            <div v-else class="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                No admin shortcuts are available for this account yet.
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
