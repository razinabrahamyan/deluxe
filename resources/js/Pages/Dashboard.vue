<template>
    <AppLayout>
        <NotificationToast ref="notificationToast" />
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-4 md:mb-6 flex flex-wrap gap-2 justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">Tasks</h1>
                <div class="flex items-center gap-2">
                    <Button class="md:hidden" variant="outline" @click="showFilters = !showFilters">
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Button v-if="isAdmin" @click="showCreateModal = true">
                        Create Task
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-card border rounded-lg p-3 md:p-4 mb-4 md:mb-6" :class="{ 'hidden md:block': !showFilters }">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
                    <div>
                        <Label for="search" class="mb-2">Search</Label>
                        <Input
                            id="search"
                            v-model="filters.search"
                            type="text"
                            placeholder="Search tasks..."
                            @input="search"
                        />
                    </div>
                    <div>
                        <Label for="status" class="mb-2">Status</Label>
                        <Select v-model="filters.status" @update:model-value="search">
                            <SelectTrigger id="status">
                                <SelectValue placeholder="All Statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Statuses</SelectItem>
                                <SelectItem v-for="status in statuses" :key="status.id" :value="String(status.id)">
                                    {{ status.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label for="user" class="mb-2">Assignee</Label>
                        <Select v-model="filters.user_id" @update:model-value="search">
                            <SelectTrigger id="user">
                                <SelectValue placeholder="All Users" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Users</SelectItem>
                                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                                    {{ user.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Task List -->
            <div class="border rounded-md bg-card">
                <ul class="divide-y divide-border">
                    <li v-for="task in tasks.data" :key="task.id" class="p-4 md:p-6 hover:bg-muted/50">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold mb-2">
                                    {{ task.title }}
                                </h3>
                                <p v-if="task.description" class="text-muted-foreground mb-3">
                                    {{ task.description }}
                                </p>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 items-center text-sm text-muted-foreground">
                                    <span>From: {{ formatDate(task.start_date) }}</span>
                                    <span>To: {{ formatDate(task.end_date) }}</span>
                                    <span>Assignee: {{ task.user.name }}</span>
                                    <span
                                        class="px-2 py-1 rounded text-white text-xs"
                                        :style="{ backgroundColor: task.status.color }"
                                    >
                                        {{ task.status.name }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="isAdmin" class="ml-4 flex gap-2">
                                <Button variant="outline" size="sm" class="px-3 py-1" @click="editTask(task)">Edit</Button>
                                <Button variant="destructive" size="sm" class="px-3 py-1" @click="deleteTask(task)">Delete</Button>
                            </div>
                        </div>
                    </li>
                </ul>

                <!-- Pagination -->
                <div v-if="tasks.links" class="p-3 md:p-4 flex flex-col md:flex-row gap-2 md:gap-0 md:justify-between md:items-center border-t">
                    <div class="text-sm text-muted-foreground">
                        Showing {{ tasks.from }} to {{ tasks.to }} of {{ tasks.total }} tasks
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Button
                            v-for="link in tasks.links"
                            :key="link.label"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            :disabled="!link.url"
                            @click="link.url && router.visit(link.url)"
                        >
                            <span v-html="link.label"></span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Task Modal -->
        <TaskCreateModal
            :is-open="showCreateModal"
            :users="users"
            :statuses="statuses"
            @close="showCreateModal = false"
        />

        <!-- Edit Task Modal -->
        <TaskEditModal
            :is-open="showEditModal"
            :task="selectedTask"
            :users="users"
            :statuses="statuses"
            @close="showEditModal = false"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import type { Task, PaginatedData, User, Status } from '@/types'
import AppLayout from '@/Layouts/AppLayout.vue'
import TaskCreateModal from '@/components/TaskCreateModal.vue'
import TaskEditModal from '@/components/TaskEditModal.vue'
import NotificationToast from '@/components/NotificationToast.vue'

interface Props {
    tasks: PaginatedData
    users: User[]
    statuses: Status[]
    filters: {
        search?: string
        status?: string
        user_id?: string
    }
}

const props = defineProps<Props>()

const page = usePage()
const isAdmin = computed(() => (page.props.auth as any)?.user?.role === 'admin')

const showCreateModal = ref(false)
const showFilters = ref(false)
const showEditModal = ref(false)
const selectedTask = ref<Task | null>(null)
const notificationToast = ref<InstanceType<typeof NotificationToast> | null>(null)

const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    user_id: props.filters.user_id || '',
})

const search = () => {
    const params: Record<string, string> = {
        search: filters.value.search || '',
        status: filters.value.status === 'all' ? '' : (filters.value.status || ''),
        user_id: filters.value.user_id === 'all' ? '' : (filters.value.user_id || ''),
    }
    router.get('/dashboard', params, {
        preserveState: true,
        preserveScroll: true,
    })
}

const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString()
}

const editTask = (task: Task) => {
    selectedTask.value = task
    showEditModal.value = true
}

const deleteTask = (task: Task) => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(`/tasks/${task.id}`)
    }
}
</script>
