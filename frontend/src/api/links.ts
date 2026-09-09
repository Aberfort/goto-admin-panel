import api from './client';
import type { ImportResult, Link } from '../types';

export interface CreateLinkPayload {
    target_url: string;
    short_code?: string;
    expires_at?: string | null;
    password?: string | null;
}

export interface UpdateLinkPayload {
    target_url?: string;
    is_active?: boolean;
    expires_at?: string | null;
    /** Omit to leave unchanged; send '' to remove the password. */
    password?: string | null;
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

export const importLinks = (siteId: number, file: File) => {
    const form = new FormData();
    form.append('file', file);

    return api
        .post<ImportResult>(`/api/sites/${siteId}/links/import`, form)
        .then((r) => r.data);
};
