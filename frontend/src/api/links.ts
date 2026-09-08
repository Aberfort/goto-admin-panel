import api from './client';
import type { Link } from '../types';

export interface CreateLinkPayload {
    target_url: string;
    short_code?: string;
}

export interface UpdateLinkPayload {
    target_url?: string;
    is_active?: boolean;
}

export const listLinks = (siteId: number) =>
    api.get<Link[]>(`/api/sites/${siteId}/links`).then((r) => r.data);

export const getLink = (id: number) => api.get<Link>(`/api/links/${id}`).then((r) => r.data);

export const createLink = (siteId: number, payload: CreateLinkPayload) =>
    api.post<Link>(`/api/sites/${siteId}/links`, payload).then((r) => r.data);

export const updateLink = (id: number, payload: UpdateLinkPayload) =>
    api.put<Link>(`/api/links/${id}`, payload).then((r) => r.data);

export const deleteLink = (id: number) => api.delete(`/api/links/${id}`);

export const toggleLink = (id: number) =>
    api.patch<Link>(`/api/links/${id}/toggle`).then((r) => r.data);
