import axios from 'axios';

// Same-origin by default (works behind the nginx proxy in docker-compose
// and in the Railway build). For local `npm run dev` against a separately
// running backend, set VITE_API_URL=http://localhost:8000 in frontend/.env.
const API_URL = import.meta.env.VITE_API_URL || '';

/**
 * Where a visitor lands: short links, QR images and the redirect endpoint are
 * served by the backend, which is a separate host from this SPA in every
 * deployment except the same-origin proxy.
 */
export const publicBaseUrl = API_URL || window.location.origin;

const api = axios.create({
    baseURL: API_URL,
});

export default api;
