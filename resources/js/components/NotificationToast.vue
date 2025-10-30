<template>
  <div
    v-if="notification"
    class="fixed top-4 right-4 z-50 max-w-md w-full bg-white border border-gray-200 rounded-lg shadow-lg p-4 transform transition-all duration-300"
    :class="{ 'translate-x-0 opacity-100': show, '-translate-x-full opacity-0': !show }"
  >
    <div class="flex items-start gap-3">
      <div class="flex-shrink-0">
        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900">
          {{ notification.reassigned ? 'Task Reassigned' : 'New Task Assigned' }}
        </p>
        <p class="text-sm text-gray-600 mt-1">{{ notification.title }}</p>
        <p class="text-xs text-gray-500 mt-1">
          {{ notification.start_date }} to {{ notification.end_date }}
        </p>
      </div>
      <button
        @click="dismiss"
        class="flex-shrink-0 text-gray-400 hover:text-gray-600"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'

interface TaskNotification {
  id: number
  title: string
  description?: string
  start_date: string
  end_date: string
  status: string
  reassigned: boolean
  assigned_at: string
}

const notification = ref<TaskNotification | null>(null)
const show = ref(false)

const authStore = useAuthStore()

let autoHideTimeout: ReturnType<typeof setTimeout> | null = null
let subscribed = false

const dismiss = () => {
  show.value = false
  setTimeout(() => {
    notification.value = null
  }, 300)
}

const displayNotification = (data: TaskNotification) => {
  notification.value = data
  show.value = true
  
  if (autoHideTimeout) {
    clearTimeout(autoHideTimeout)
  }
  
  autoHideTimeout = setTimeout(() => {
    dismiss()
  }, 10000)
}

const subscribeToNotifications = () => {
  // Prevent multiple subscriptions
  if (subscribed) {
    return
  }
  
  const userId = authStore.userId
  
  console.log('NotificationToast: Attempting subscription');
  console.log('User ID from store:', userId);
  console.log('window.Echo:', (window as any).Echo);
  
  // Only subscribe if user is logged in
  if (!userId) {
    console.log('No user logged in, skipping notification subscription');
    return
  }
  
  // Check if Echo is available
  if (!(window as any).Echo || !(window as any).Echo.private) {
    console.log('Echo not initialized or private method not available');
    return
  }
  
  // Subscribe to user's private channel
  console.log('Subscribing to private channel:', `user.${userId}`)
  try {
    (window as any).Echo.private(`user.${userId}`)
      .listen('.task.assigned', (data: TaskNotification) => {
        console.log('Received task.assigned event:', data)
        displayNotification(data)
      })
    
    subscribed = true
    console.log('Successfully subscribed to notifications')
  } catch (error) {
    console.error('Failed to subscribe to notifications:', error)
  }
}

// Watch for auth changes and subscribe when user is logged in
watch(() => authStore.userId, (newUserId) => {
  if (newUserId && !subscribed) {
    console.log('User logged in, attempting subscription')
    subscribeToNotifications()
  } else if (!newUserId && subscribed) {
    console.log('User logged out, unsubscribing')
    subscribed = false
  }
}, { immediate: true })

onMounted(() => {
  console.log('NotificationToast mounted')
  console.log('Current auth state:', {
    isAuthenticated: authStore.isAuthenticated,
    userId: authStore.userId,
    user: authStore.user
  })
  
  // Try to subscribe immediately if user is already authenticated
  subscribeToNotifications()
})

defineExpose({
  displayNotification
})
</script>

