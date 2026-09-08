import axios from 'axios';

// Same-origin by default (works behind the nginx proxy used in docker-compose
// and in the Railway/production build). For local `npm start` without nginx,
// set REACT_APP_API_URL=http://localhost:8000 in frontend/.env.
const API_URL = process.env.REACT_APP_API_URL || '';

const api = axios.create({
    baseURL: API_URL,
    withCredentials: true,
    // Needed so axios attaches X-XSRF-TOKEN even when the API is on a
    // different origin/port than the frontend (e.g. local dev: 3000 vs 8000).
    withXSRFToken: true,
});

export default api;
