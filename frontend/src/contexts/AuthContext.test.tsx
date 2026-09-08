import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { AuthProvider } from './AuthContext';
import { useAuth } from './useAuth';
import * as authApi from '../api/auth';

vi.mock('../api/auth');
vi.mock('react-toastify', () => ({ toast: { success: vi.fn(), error: vi.fn() } }));

function TestConsumer() {
    const { user, loading, login, logout } = useAuth();

    if (loading) return <div>loading</div>;

    return (
        <div>
            <div data-testid="user">{user ? user.name : 'none'}</div>
            <button onClick={() => login({ email: 'a@b.com', password: 'secret' })}>login</button>
            <button onClick={() => logout()}>logout</button>
        </div>
    );
}

describe('AuthProvider', () => {
    beforeEach(() => {
        localStorage.clear();
        vi.clearAllMocks();
    });

    it('starts with no user when there is no stored token', async () => {
        render(
            <AuthProvider>
                <TestConsumer />
            </AuthProvider>
        );

        await waitFor(() => expect(screen.getByTestId('user')).toHaveTextContent('none'));
    });

    it('logs in and stores the token in localStorage', async () => {
        vi.mocked(authApi.login).mockResolvedValue({
            user: { id: 1, name: 'Alice', email: 'a@b.com', is_demo: false },
            token: 'test-token',
        });

        render(
            <AuthProvider>
                <TestConsumer />
            </AuthProvider>
        );
        await waitFor(() => expect(screen.getByTestId('user')).toHaveTextContent('none'));

        await userEvent.click(screen.getByText('login'));

        await waitFor(() => expect(screen.getByTestId('user')).toHaveTextContent('Alice'));
        expect(localStorage.getItem('token')).toBe('test-token');
    });

    it('logs out and clears the stored token even if the API call fails', async () => {
        vi.mocked(authApi.login).mockResolvedValue({
            user: { id: 1, name: 'Alice', email: 'a@b.com', is_demo: false },
            token: 'test-token',
        });
        vi.mocked(authApi.logout).mockRejectedValue(new Error('token already expired'));

        render(
            <AuthProvider>
                <TestConsumer />
            </AuthProvider>
        );
        await waitFor(() => expect(screen.getByTestId('user')).toHaveTextContent('none'));
        await userEvent.click(screen.getByText('login'));
        await waitFor(() => expect(screen.getByTestId('user')).toHaveTextContent('Alice'));

        await userEvent.click(screen.getByText('logout'));

        await waitFor(() => expect(screen.getByTestId('user')).toHaveTextContent('none'));
        expect(localStorage.getItem('token')).toBeNull();
    });
});
