<template>
  <Dialog :open="isOpen" @update:open="updateOpen">
    <DialogContent class="sm:max-w-2xl w-[calc(100%-2rem)]">
      <DialogHeader>
        <DialogTitle>Create New Task</DialogTitle>
        <DialogDescription>Fill in the details to create a new task</DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <Label for="title" class="mb-2">Title</Label>
          <Input
            id="title"
            v-model="formData.title"
            type="text"
            placeholder="Enter task title"
            :class="errors.title ? 'border-destructive' : ''"
          />
          <p v-if="errors.title" class="text-sm text-destructive mt-1">{{ errors.title }}</p>
        </div>

        <div>
          <Label for="description" class="mb-2">Description</Label>
          <textarea
            id="description"
            v-model="formData.description"
            rows="4"
            class="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring"
            placeholder="Enter task description"
          />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label for="start_date" class="mb-2">Start Date</Label>
            <Input
              id="start_date"
              v-model="formData.start_date"
              type="date"
              :class="errors.start_date ? 'border-destructive' : ''"
            />
            <p v-if="errors.start_date" class="text-sm text-destructive mt-1">{{ errors.start_date }}</p>
          </div>

          <div>
            <Label for="end_date" class="mb-2">End Date</Label>
            <Input
              id="end_date"
              v-model="formData.end_date"
              type="date"
              :min="formData.start_date"
              :class="errors.end_date ? 'border-destructive' : ''"
            />
            <p v-if="errors.end_date" class="text-sm text-destructive mt-1">{{ errors.end_date }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label for="user_id" class="mb-2">Assigned User</Label>
            <Select v-model="formData.user_id" class="w-full">
              <SelectTrigger :class="errors.user_id ? 'border-destructive' : ''" class="w-full">
                <SelectValue placeholder="Select a user" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">
                  {{ user.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="errors.user_id" class="text-sm text-destructive mt-1">{{ errors.user_id }}</p>
          </div>

          <div>
            <Label for="status_id" class="mb-2">Status</Label>
            <Select v-model="formData.status_id" class="w-full">
              <SelectTrigger :class="errors.status_id ? 'border-destructive' : ''" class="w-full">
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="status in statuses" :key="status.id" :value="String(status.id)">
                  {{ status.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="errors.status_id" class="text-sm text-destructive mt-1">{{ errors.status_id }}</p>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="handleClose">Cancel</Button>
          <Button type="submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Creating...' : 'Create Task' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
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
import type { User, Status } from '@/types'

interface Props {
  isOpen: boolean
  users: User[]
  statuses: Status[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
}>()

interface TaskFormData {
  title: string
  description: string
  start_date: string
  end_date: string
  user_id: string
  status_id: string
}

const formData = ref<TaskFormData>({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  user_id: '',
  status_id: '',
})

const isSubmitting = ref(false)
const errors = ref<Partial<Record<keyof TaskFormData, string>>>({})

const updateOpen = (open: boolean) => {
  if (!open) {
    handleClose()
  }
}

const handleClose = () => {
  formData.value = {
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    user_id: '',
    status_id: '',
  }
  errors.value = {}
  emit('close')
}

const handleSubmit = () => {
  isSubmitting.value = true
  errors.value = {}

  router.post('/tasks', formData.value, {
    onSuccess: () => {
      handleClose()
      isSubmitting.value = false
    },
    onError: (validationErrors: any) => {
      errors.value = validationErrors
      isSubmitting.value = false
    },
  })
}
</script>
