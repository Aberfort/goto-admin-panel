export interface User {
    id: number;
    name: string;
    email: string;
    is_demo: boolean;
}

export interface Site {
    id: number;
    user_id: number;
    name: string;
    domain: string | null;
    description: string | null;
    links_count?: number;
    created_at: string;
    updated_at: string;
}

export interface Link {
    id: number;
    site_id: number;
    short_code: string;
    target_url: string;
    is_active: boolean;
    clicks_count: number;
    created_at: string;
    updated_at: string;
}

export interface TimeseriesPoint {
    date: string;
    clicks: number;
}

export interface Breakdown {
    label: string;
    clicks: number;
}

export interface TopLink {
    id: number;
    short_code: string;
    target_url: string;
    clicks_count: number;
}

export interface Analytics {
    timeseries: TimeseriesPoint[];
    referrers: Breakdown[];
    browsers: Breakdown[];
    devices: Breakdown[];
    top_links?: TopLink[];
}

export interface AppConfig {
    registration_enabled: boolean;
}

export interface ApiErrorPayload {
    message?: string;
    errors?: Record<string, string[]>;
}
