import api from './client';
import type { AppConfig } from '../types';

export const fetchAppConfig = () => api.get<AppConfig>('/api/config').then((r) => r.data);
