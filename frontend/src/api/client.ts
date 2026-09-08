import axios from 'axios';

// Same-origin by default (works behind the nginx proxy in docker-compose
// and in the Railway build). For local `npm run dev` against a separately
// running backend, set VITE_API_URL=http://localhost:8000 in frontend/.env.
const API_URL = import.meta.env.VITE_API_URL || '';

const api = axios.create({
    baseURL: API_URL,
});

export default api;
