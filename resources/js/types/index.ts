export * from './auth';
export * from './navigation';
export * from './ui';

export interface Bot {
    id: number;
    name: string;
    telegram_token: string;
    telegram_username: string;
    system_prompt: string;
    is_active: boolean;
    avatar_url?: string | null;
    messages_count?: number;
    created_at: string;
    updated_at: string;
}

export interface TelegramUser {
    id: number;
    telegram_id: number;
    first_name: string | null;
    username: string | null;
    free_messages_left: number;
    paid_credits: number;
    messages_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Message {
    id: number;
    telegram_user_id: number;
    bot_id: number | null;
    role: 'user' | 'assistant';
    content: string;
    created_at: string;
    bot?: Bot;
    telegram_user?: TelegramUser;
}

export interface ImagePrompt {
    id: number;
    label: string;
    prompt: string;
    negative_prompt: string | null;
    created_at: string;
    updated_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

export interface DashboardStats {
    total_bots: number;
    active_bots: number;
    total_users: number;
    total_messages: number;
    messages_today: number;
}