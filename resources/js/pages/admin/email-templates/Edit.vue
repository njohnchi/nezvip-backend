<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface EmailTemplate {
    id: number;
    key: string;
    name: string;
    description: string | null;
    subject: string;
    body: string;
    is_active: boolean;
    available_variables: string[];
}

interface Props {
    template: EmailTemplate;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Email Templates',
        href: '/admin/email-templates',
    },
    {
        title: 'Edit',
        href: `/admin/email-templates/${props.template.id}/edit`,
    },
];

const form = useForm({
    subject: props.template.subject,
    body: props.template.body,
    is_active: props.template.is_active,
});

const handleSubmit = () => {
    form.put(`/admin/email-templates/${props.template.id}`, {
        preserveScroll: true,
    });
};

const toggleActive = (value: unknown) => {
    form.is_active = Boolean(value);
};
</script>

<template>
    <Head :title="`Edit ${template.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="router.visit('/admin/email-templates')">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ template.name }}</h1>
                    <p class="text-sm text-muted-foreground">{{ template.description }}</p>
                </div>
            </div>

            <form class="space-y-4" @submit.prevent="handleSubmit">
                <Card>
                    <CardHeader>
                        <CardTitle>Template Information</CardTitle>
                        <CardDescription>
                            Template key: <code class="rounded bg-muted px-2 py-1 text-xs">{{ template.key }}</code>
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="subject">Subject</Label>
                            <Input id="subject" v-model="form.subject" />
                            <p v-if="form.errors.subject" class="text-sm text-destructive">{{ form.errors.subject }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="body">Email Body</Label>
                            <Textarea
                                id="body"
                                v-model="form.body"
                                rows="14"
                                class="font-mono text-sm"
                            />
                            <p v-if="form.errors.body" class="text-sm text-destructive">{{ form.errors.body }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_active"
                                :model-value="form.is_active"
                                :true-value="true"
                                :false-value="false"
                                @update:model-value="toggleActive"
                            />
                            <Label for="is_active" class="cursor-pointer font-normal">
                                Template is active and should send acknowledgement emails
                            </Label>
                        </div>
                        <p v-if="form.errors.is_active" class="text-sm text-destructive">{{ form.errors.is_active }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Available Variables</CardTitle>
                        <CardDescription>
                            Use variables in your content with double braces, for example
                            <code v-text="'{{name}}'" />.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-2 md:grid-cols-2">
                            <div
                                v-for="variable in template.available_variables"
                                :key="variable"
                                class="rounded border bg-muted/30 px-3 py-2 font-mono text-sm"
                                v-text="`{{${variable}}}`"
                            >
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="router.visit('/admin/email-templates')">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="form.processing">Save Template</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>


