// resources/js/api.ts
import axios from 'axios';

const api = axios.create({
    baseURL: '/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// CSRF Token
api.interceptors.request.use((config) => {
    const token = document.querySelector<HTMLMetaElement>(
        'meta[name="csrf-token"]',
    )?.content;
    if (token) config.headers['X-CSRF-TOKEN'] = token;
    return config;
});

export default api;
