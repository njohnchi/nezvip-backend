<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { MoreHorizontal, Pencil, Plus, Trash2, UserPlus } from 'lucide-vue-next';
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

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    roles: Role[];
    created_at: string;
}

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Users',
        href: '/admin/users',
    },
];

const deleteUser = (userId: number) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/admin/users/${userId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Users Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Users</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage system users and their roles
                    </p>
                </div>
                <Link href="/admin/users/create">
                    <Button>
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add User
                    </Button>
                </Link>
            </div>

            <div class="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Roles</TableHead>
                            <TableHead>Created At</TableHead>
                            <TableHead class="w-20"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="user in users.data"
                            :key="user.id"
                        >
                            <TableCell class="font-medium">
                                {{ user.name }}
                            </TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        variant="secondary"
                                    >
                                        {{ role.name }}
                                    </Badge>
                                    <span
                                        v-if="user.roles.length === 0"
                                        class="text-sm text-muted-foreground"
                                    >
                                        No roles
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </TableCell>
                            <TableCell>
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        as-child
                                        :disabled="user.roles.some(role => role.name === 'Super Admin')"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            :disabled="user.roles.some(role => role.name === 'Super Admin')"
                                        >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <Link :href="`/admin/users/${user.id}/edit`">
                                            <DropdownMenuItem>
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </DropdownMenuItem>
                                        </Link>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            class="text-destructive focus:text-destructive"
                                            @click="deleteUser(user.id)"
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

            <div
                v-if="users.total > users.per_page"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ (users.current_page - 1) * users.per_page + 1 }} to
                    {{ Math.min(users.current_page * users.per_page, users.total) }}
                    of {{ users.total }} users
                </p>
            </div>
        </div>
    </AppLayout>
</template>



