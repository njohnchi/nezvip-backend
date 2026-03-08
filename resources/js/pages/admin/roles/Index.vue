<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Pencil, Shield, Trash2 } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
    users_count: number;
}

interface Props {
    roles: Role[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Roles',
        href: '/admin/roles',
    },
];

const deleteRole = (roleId: number) => {
    if (confirm('Are you sure you want to delete this role?')) {
        router.delete(`/admin/roles/${roleId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Roles Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Roles</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage roles and their permissions
                    </p>
                </div>
                <Link href="/admin/roles/create">
                    <Button>
                        <Shield class="mr-2 h-4 w-4" />
                        Add Role
                    </Button>
                </Link>
            </div>

            <div class="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Permissions</TableHead>
                            <TableHead>Users</TableHead>
                            <TableHead class="w-20"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="role in roles"
                            :key="role.id"
                        >
                            <TableCell class="font-medium">
                                {{ role.name }}
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="permission in role.permissions"
                                        :key="permission.id"
                                        variant="outline"
                                    >
                                        {{ permission.name }}
                                    </Badge>
                                    <span
                                        v-if="role.permissions.length === 0"
                                        class="text-sm text-muted-foreground"
                                    >
                                        No permissions
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                {{ role.users_count }} {{ role.users_count === 1 ? 'user' : 'users' }}
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                        >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <Link :href="`/admin/roles/${role.id}/edit`">
                                            <DropdownMenuItem>
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </DropdownMenuItem>
                                        </Link>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            v-if="role.name !== 'Super Admin'"
                                            class="text-destructive focus:text-destructive"
                                            @click="deleteRole(role.id)"
                                        >
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Delete
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>



