import api from './client';
import type { User } from '../types';

export interface AuthResponse {
    user: User;
    token: string;
}

export interface RegisterPayload {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

export interface LoginPayload {
    email: string;
    password: string;
}

export const register = (payload: RegisterPayload) =>
    api.post<AuthResponse>('/api/register', payload).then((r) => r.data);

export const login = (payload: LoginPayload) =>
    api.post<AuthResponse>('/api/login', payload).then((r) => r.data);

export const logout = () => api.post('/api/logout');

export const fetchUser = () => api.get<User>('/api/user').then((r) => r.data);
