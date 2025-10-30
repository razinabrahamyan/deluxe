/// <reference types="vite/client" />

import 'vue-router'

declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

declare global {
  interface Window {
    Echo: any
    userId: number | null
  }
}

declare module '@inertiajs/core' {
  interface PageProps {
    auth?: {
      user?: {
        id: number
        name: string
        email: string
        role?: string
      }
    }
    pusher?: {
      key?: string
      cluster?: string
    }
  }
}

