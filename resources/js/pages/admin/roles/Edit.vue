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

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Props {
    role: Role;
    permissions: Permission[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/admin/roles',
    },
    {
        title: 'Edit',
        href: `/admin/roles/${props.role.id}/edit`,
    },
];

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions.map(p => p.name),
});

const handleSubmit = () => {
    form.put(`/admin/roles/${props.role.id}`, {
        preserveScroll: true,
    });
};

const togglePermission = (permissionName: string) => {
    const index = form.permissions.indexOf(permissionName);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionName);
    }
};

// Group permissions by category
const groupedPermissions = props.permissions.reduce((acc, permission) => {
    const category = permission.name.split(' ').pop() || 'other';
    if (!acc[category]) {
        acc[category] = [];
    }
    acc[category].push(permission);
    return acc;
}, {} as Record<string, Permission[]>);
</script>

<template>
    <Head title="Edit Role" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button
                    variant="ghost"
                    size="icon"
                    @click="router.visit('/admin/roles')"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Edit Role</h1>
                    <p class="text-sm text-muted-foreground">
                        Update role information and permissions
                    </p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="grid gap-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Role Information</CardTitle>
                            <CardDescription>
                                Basic information about the role
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Role Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="e.g., Editor, Moderator"
                                    :class="{ 'border-destructive': form.errors.name }"
                                    :disabled="role.name === 'Super Admin'"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.name }}
                                </p>
                                <p
                                    v-if="role.name === 'Super Admin'"
                                    class="text-sm text-muted-foreground"
                                >
                                    Super Admin role name cannot be changed
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Permissions</CardTitle>
                            <CardDescription>
                                Select the permissions for this role
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="role.name === 'Super Admin'"
                                class="rounded-lg border border-muted bg-muted/50 p-4"
                            >
                                <p class="text-sm text-muted-foreground">
                                    Super Admin has all permissions by default and cannot be modified.
                                </p>
                            </div>
                            <div
                                v-else
                                class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                            >
                                <div
                                    v-for="(perms, category) in groupedPermissions"
                                    :key="category"
                                    class="space-y-3"
                                >
                                    <h3 class="font-semibold capitalize">{{ category }}</h3>
                                    <div class="space-y-2">
                                        <div
                                            v-for="permission in perms"
                                            :key="permission.id"
                                            class="flex items-center space-x-2"
                                        >
                                            <Checkbox
                                                :id="`permission-${permission.id}`"
                                                :checked="form.permissions.includes(permission.name)"
                                                @update:checked="togglePermission(permission.name)"
                                            />
                                            <Label
                                                :for="`permission-${permission.id}`"
                                                class="cursor-pointer font-normal"
                                            >
                                                {{ permission.name }}
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p
                                v-if="form.errors.permissions"
                                class="mt-2 text-sm text-destructive"
                            >
                                {{ form.errors.permissions }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="router.visit('/admin/roles')"
                    >
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        Update Role
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

