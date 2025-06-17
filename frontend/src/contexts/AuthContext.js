import React, { createContext, useState, useEffect } from 'react';
import api from '../services/api';
import { toast } from 'react-toastify';

export const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [token, setToken] = useState(localStorage.getItem('token') || '');

    const fetchUser = async () => {
        if (token) {
            try {
                api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                const response = await api.get('/api/user');
                setUser(response.data);
            } catch (error) {
                setUser(null);
                setToken('');
                localStorage.removeItem('token');
            }
        }
        setLoading(false);
    };

    useEffect(() => {
        fetchUser();
    }, [token]);

    const login = async (credentials) => {
        try {
            await api.get('/sanctum/csrf-cookie');
            const response = await api.post('/api/login', credentials);
            setUser(response.data.user);
            setToken(response.data.token);
            localStorage.setItem('token', response.data.token);
            toast.success('Вхід успішний!');
        } catch (error) {
            toast.error(error.response?.data?.message || 'Помилка при вході.');
            throw error;
        }
    };

    const register = async (data) => {
        try {
            const response = await api.post('/api/register', data);
            setUser(response.data.user);
            setToken(response.data.token);
            localStorage.setItem('token', response.data.token);
            toast.success('Реєстрація успішна!');
        } catch (error) {
            toast.error(error.response?.data?.message || 'Помилка при реєстрації.');
            throw error;
        }
    };


    const logout = async () => {
        try {
            await api.post('/api/logout');
            setUser(null);
            setToken('');
            localStorage.removeItem('token');
            delete api.defaults.headers.common['Authorization'];
        } catch (error) {
            toast.error('Помилка при виході.');
            throw error;
        }
    };

    return (
        <AuthContext.Provider value={{ user, loading, login, register, logout }}>
            {children}
        </AuthContext.Provider>
    );
};
