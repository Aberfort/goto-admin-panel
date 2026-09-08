import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { MemoryRouter } from 'react-router-dom';
import SitesPage from './SitesPage';
import { useAuth } from '../contexts/useAuth';
import * as sitesApi from '../api/sites';
import type { Site } from '../types';

vi.mock('../contexts/useAuth');
vi.mock('../api/sites');
vi.mock('react-toastify', () => ({ toast: { success: vi.fn(), error: vi.fn() } }));

const site: Site = {
    id: 1,
    user_id: 1,
    name: 'My Site',
    domain: 'example.com',
    description: null,
    links_count: 3,
    created_at: '2026-01-01T00:00:00Z',
    updated_at: '2026-01-01T00:00:00Z',
};

function renderPage() {
    return render(
        <MemoryRouter>
            <SitesPage />
        </MemoryRouter>
    );
}

describe('SitesPage', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        vi.mocked(useAuth).mockReturnValue({
            user: { id: 1, name: 'User', email: 'u@example.com', is_demo: false },
            loading: false,
            login: vi.fn(),
            register: vi.fn(),
            logout: vi.fn(),
        });
    });

    it('lists the users sites', async () => {
        vi.mocked(sitesApi.listSites).mockResolvedValue([site]);

        renderPage();

        expect(await screen.findByText('My Site')).toBeInTheDocument();
        expect(screen.getByText('example.com')).toBeInTheDocument();
    });

    it('shows an empty state when there are no sites', async () => {
        vi.mocked(sitesApi.listSites).mockResolvedValue([]);

        renderPage();

        expect(await screen.findByText('Сайтів поки немає.')).toBeInTheDocument();
    });

    it('creates a site through the dialog', async () => {
        vi.mocked(sitesApi.listSites).mockResolvedValue([]);
        vi.mocked(sitesApi.createSite).mockResolvedValue({ ...site, name: 'New Site' });

        renderPage();
        await screen.findByText('Сайтів поки немає.');

        await userEvent.click(screen.getByRole('button', { name: 'Додати сайт' }));
        await userEvent.type(screen.getByLabelText('Назва'), 'New Site');
        await userEvent.click(screen.getByRole('button', { name: 'Зберегти' }));

        await waitFor(() =>
            expect(sitesApi.createSite).toHaveBeenCalledWith(
                expect.objectContaining({ name: 'New Site' })
            )
        );
    });

    it('disables write actions for the demo account', async () => {
        vi.mocked(useAuth).mockReturnValue({
            user: { id: 1, name: 'Demo', email: 'demo@linkfleet.app', is_demo: true },
            loading: false,
            login: vi.fn(),
            register: vi.fn(),
            logout: vi.fn(),
        });
        vi.mocked(sitesApi.listSites).mockResolvedValue([site]);

        renderPage();
        await screen.findByText('My Site');

        expect(screen.getByRole('button', { name: 'Додати сайт' })).toBeDisabled();
        expect(screen.getByText(/лише для читання/i)).toBeInTheDocument();
    });
});
