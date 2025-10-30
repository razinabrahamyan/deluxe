export interface PageProps {
    auth?: {
        user?: {
            id: number
            name: string
            email: string
            role?: string
        }
    }
}

export interface Task {
    id: number
    title: string
    description?: string
    start_date: string
    end_date: string
    user_id: number
    status_id: number
    user: {
        id: number
        name: string
        email: string
    }
    status: {
        id: number
        name: string
        color: string
    }
}

export interface User {
    id: number
    name: string
    email: string
}

export interface Status {
    id: number
    name: string
    color: string
}

export interface PaginatedData {
    data: Task[]
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
    from: number
    to: number
    total: number
}

