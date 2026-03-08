<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    roles: Role[];
}

interface Props {
    user: User;
    roles: Role[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Users',
        href: '/admin/users',
    },
    {
        title: 'Edit',
        href: `/admin/users/${props.user.id}/edit`,
    },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles.map(r => r.name),
});

const handleSubmit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button
                    variant="ghost"
                    size="icon"
                    @click="router.visit('/admin/users')"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Edit User</h1>
                    <p class="text-sm text-muted-foreground">
                        Update user information and roles
                    </p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="grid gap-4 md:grid-cols-2">
                    <Card class="md:col-span-1">
                        <CardHeader>
                            <CardTitle>User Information</CardTitle>
                            <CardDescription>
                                Basic information about the user
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="John Doe"
                                    :class="{ 'border-destructive': form.errors.name }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="john@example.com"
                                    :class="{ 'border-destructive': form.errors.email }"
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="password">Password</Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Leave blank to keep current password"
                                    :class="{ 'border-destructive': form.errors.password }"
                                />
                                <p
                                    v-if="form.errors.password"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.password }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Leave blank to keep the current password
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="md:col-span-1">
                        <CardHeader>
                            <CardTitle>Roles</CardTitle>
                            <CardDescription>
                                Assign roles to this user
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div
                                v-if="user.roles.some(r => r.name === 'Super Admin')"
                                class="rounded-lg border border-muted bg-muted/50 p-4"
                            >
                                <p class="text-sm font-medium">This user has Super Admin role</p>
                                <p class="text-sm text-muted-foreground">
                                    Super Admin role cannot be removed or modified.
                                </p>
                            </div>
                            <div
                                v-for="role in roles"
                                :key="role.id"
                                class="flex items-center space-x-2"
                            >
                                <Checkbox
                                    :id="`role-${role.id}`"
                                    :model-value="form.roles.includes(role.name)"
                                    @update:model-value="(checked) => {
                                        const index = form.roles.indexOf(role.name);
                                        if (checked && index === -1) {
                                            form.roles.push(role.name);
                                        } else if (!checked && index > -1) {
                                            form.roles.splice(index, 1);
                                        }
                                    }"
                                />
                                <Label
                                    :for="`role-${role.id}`"
                                    class="cursor-pointer font-normal"
                                >
                                    {{ role.name }}
                                </Label>
                            </div>
                            <p
                                v-if="form.errors.roles"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.roles }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit('/admin/users')"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        Update User
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

