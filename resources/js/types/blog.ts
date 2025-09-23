// Типы для блога

export interface BlogPost {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content?: string; // Доступно только на странице просмотра
    featured_image: string | null;
    status: string;
    published_at: string | null;
    views_count: number;
    reading_time: number;
    user_id?: number; // ID пользователя для проверки прав доступа
    user: {
        id: number;
        name: string;
    };
    category: {
        id: number;
        name: string;
        slug: string;
        color: string;
    };
}

export interface BlogCategory {
    id: number;
    name: string;
    slug: string;
    color: string;
    posts_count: number;
}

// Типы для Laravel пагинации
export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface LaravelPagination<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

// Типы для фильтров
export interface BlogFilters {
    search?: string;
    category?: string;
    status?: string;
}

// Пропсы для страниц блога
export interface BlogPostsIndexProps {
    posts: LaravelPagination<BlogPost>;
    categories: BlogCategory[];
    filters: BlogFilters;
}

export interface BlogPostsCreateProps {
    categories: BlogCategory[];
}

export interface BlogPostsShowProps {
    post: BlogPost;
    relatedPosts: BlogPost[];
}
