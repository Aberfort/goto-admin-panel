import api from './client';
import type { Analytics } from '../types';

export const siteAnalytics = (siteId: number) =>
    api.get<Analytics>(`/api/sites/${siteId}/analytics`).then((r) => r.data);

export const linkAnalytics = (linkId: number) =>
    api.get<Analytics>(`/api/links/${linkId}/analytics`).then((r) => r.data);
