import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'

interface User {
  id: number
  name: string
  email: string
  role?: string
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  
  const userId = computed(() => user.value?.id || null)
  const isAuthenticated = computed(() => user.value !== null)
  const isAdmin = computed(() => user.value?.role === 'admin')
  
  const setUser = (newUser: User | null) => {
    user.value = newUser
  }
  
  const logout = () => {
    user.value = null
  }
  
  return {
    user,
    userId,
    isAuthenticated,
    isAdmin,
    setUser,
    logout
  }
})

