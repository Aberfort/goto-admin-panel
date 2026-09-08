import React, { createContext, useState, useEffect } from 'react';
import { toast } from 'react-toastify';
import api from '../api/client';
import * as authApi from '../api/auth';
import { errorMessage } from '../api/errors';
import type { User } from '../types';
import type { RegisterPayload, LoginPayload } from '../api/auth';

interface AuthContextValue {
    user: User | null;
    loading: boolean;
    login: (credentials: LoginPayload) => Promise<void>;
    register: (data: RegisterPayload) => Promise<void>;
    logout: () => Promise<void>;
}

export const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export const AuthProvider = ({ children }: { children: React.ReactNode }) => {
    const [user, setUser] = useState<User | null>(null);
    const [loading, setLoading] = useState(true);
    const [token, setToken] = useState<string>(() => localStorage.getItem('token') || '');

    // Keeps every request's default Authorization header in sync with the
    // current token - not just the bootstrap fetch below, but also the
    // token login()/register() set directly from their own response.
    useEffect(() => {
        if (token) {
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        } else {
            delete api.defaults.headers.common['Authorization'];
        }
    }, [token]);

    // Runs once, to rehydrate from a token already in localStorage on page
    // load. login()/register() already receive the fresh user object in
    // their own response, so re-running this on every token change would
    // just be a redundant duplicate request.
    useEffect(() => {
        const storedToken = localStorage.getItem('token');
        if (!storedToken) {
            setLoading(false);
            return;
        }

        authApi
            .fetchUser()
            .then(setUser)
            .catch(() => {
                setUser(null);
                setToken('');
                localStorage.removeItem('token');
            })
            .finally(() => setLoading(false));
    }, []);

    const login = async (credentials: LoginPayload) => {
        try {
            const response = await authApi.login(credentials);
            setUser(response.user);
            setToken(response.token);
            localStorage.setItem('token', response.token);
            toast.success('Вхід успішний!');
        } catch (error) {
            toast.error(errorMessage(error, 'Помилка при вході.'));
            throw error;
        }
    };

    const register = async (data: RegisterPayload) => {
        try {
            const response = await authApi.register(data);
            setUser(response.user);
            setToken(response.token);
            localStorage.setItem('token', response.token);
            toast.success('Реєстрація успішна!');
        } catch (error) {
            toast.error(errorMessage(error, 'Помилка при реєстрації.'));
            throw error;
        }
    };

    const logout = async () => {
        try {
            await authApi.logout();
        } catch {
            // Token may already be invalid server-side - clear local state
            // regardless so the user isn't stuck unable to log out.
        }
        setUser(null);
        setToken('');
        localStorage.removeItem('token');
        delete api.defaults.headers.common['Authorization'];
    };

    return (
        <AuthContext.Provider value={{ user, loading, login, register, logout }}>
            {children}
        </AuthContext.Provider>
    );
};
