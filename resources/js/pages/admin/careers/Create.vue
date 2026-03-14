<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Careers', href: '/admin/careers' },
    { title: 'Create', href: '/admin/careers/create' },
];

const form = useForm({
    venture_name: '',
    role_title: '',
    location: '',
    engagement_type: '',
    venture_context: '',
    responsibilities: '',
    execution_expectations: '',
    is_active: true,
});

const submit = () => {
    form.post('/admin/careers');
};
</script>

<template>
    <Head title="Create Career Opportunity" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-semibold">Create Career Opportunity</h1>
                <p class="text-sm text-muted-foreground">Post a new venture role or opportunity.</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Opportunity Details</CardTitle>
                </CardHeader>
                <CardContent>
                    <form class="space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="venture_name">Venture / Program Name</Label>
                                <Input id="venture_name" v-model="form.venture_name" placeholder="Gasmeat Bistro" />
                                <p v-if="form.errors.venture_name" class="text-sm text-destructive">{{ form.errors.venture_name }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="role_title">Role Title</Label>
                                <Input id="role_title" v-model="form.role_title" placeholder="Operations Lead" />
                                <p v-if="form.errors.role_title" class="text-sm text-destructive">{{ form.errors.role_title }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="location">Location</Label>
                                <Input id="location" v-model="form.location" placeholder="Lagos, Nigeria or Remote" />
                                <p v-if="form.errors.location" class="text-sm text-destructive">{{ form.errors.location }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="engagement_type">Engagement Type</Label>
                                <Select v-model="form.engagement_type">
                                    <SelectTrigger id="engagement_type">
                                        <SelectValue placeholder="Select type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="Full-time">Full-time</SelectItem>
                                        <SelectItem value="Part-time">Part-time</SelectItem>
                                        <SelectItem value="Contract">Contract</SelectItem>
                                        <SelectItem value="Retainer">Retainer</SelectItem>
                                        <SelectItem value="Internship">Internship</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.engagement_type" class="text-sm text-destructive">{{ form.errors.engagement_type }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="venture_context">Venture Context</Label>
                            <Textarea
                                id="venture_context"
                                v-model="form.venture_context"
                                rows="4"
                                placeholder="Describe the venture and why this role exists..."
                            />
                            <p v-if="form.errors.venture_context" class="text-sm text-destructive">{{ form.errors.venture_context }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="responsibilities">Role Responsibilities</Label>
                            <Textarea
                                id="responsibilities"
                                v-model="form.responsibilities"
                                rows="5"
                                placeholder="Concise outline of execution responsibilities..."
                            />
                            <p v-if="form.errors.responsibilities" class="text-sm text-destructive">{{ form.errors.responsibilities }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="execution_expectations">Execution Expectations</Label>
                            <Textarea
                                id="execution_expectations"
                                v-model="form.execution_expectations"
                                rows="4"
                                placeholder="What delivery, accountability, and performance look like..."
                            />
                            <p v-if="form.errors.execution_expectations" class="text-sm text-destructive">{{ form.errors.execution_expectations }}</p>
                        </div>

                        <label class="flex items-center gap-2 text-sm">
                            <input v-model="form.is_active" type="checkbox" class="h-4 w-4" />
                            Publish immediately (visible on careers page)
                        </label>

                        <div class="flex gap-2">
                            <Button type="submit" :disabled="form.processing">Create Opportunity</Button>
                            <Link href="/admin/careers">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

