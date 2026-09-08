import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import LoginPage from './LoginPage';
import { useAuth } from '../contexts/useAuth';

vi.mock('../contexts/useAuth');

const mockNavigate = vi.fn();
vi.mock('react-router-dom', async () => {
    const actual = await vi.importActual('react-router-dom');
    return { ...actual, useNavigate: () => mockNavigate };
});

describe('LoginPage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('shows validation errors for empty fields', async () => {
        vi.mocked(useAuth).mockReturnValue({
            user: null,
            loading: false,
            login: vi.fn(),
            register: vi.fn(),
            logout: vi.fn(),
        });

        render(
            <MemoryRouter>
                <LoginPage />
            </MemoryRouter>
        );

        await userEvent.click(screen.getByRole('button', { name: 'Вхід' }));

        expect(await screen.findByText("Email є обов'язковим")).toBeInTheDocument();
        expect(await screen.findByText("Пароль є обов'язковим")).toBeInTheDocument();
    });

    it('calls login with the entered credentials and navigates on success', async () => {
        const login = vi.fn().mockResolvedValue(undefined);
        vi.mocked(useAuth).mockReturnValue({
            user: null,
            loading: false,
            login,
            register: vi.fn(),
            logout: vi.fn(),
        });

        render(
            <MemoryRouter>
                <LoginPage />
            </MemoryRouter>
        );

        await userEvent.type(screen.getByLabelText('Email'), 'user@example.com');
        await userEvent.type(screen.getByLabelText('Пароль'), 'password123');
        await userEvent.click(screen.getByRole('button', { name: 'Вхід' }));

        await waitFor(() =>
            expect(login).toHaveBeenCalledWith({ email: 'user@example.com', password: 'password123' })
        );
        await waitFor(() => expect(mockNavigate).toHaveBeenCalledWith('/'));
    });
});
