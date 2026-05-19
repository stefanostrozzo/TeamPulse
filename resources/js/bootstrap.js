import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

/**
 * Attach the Echo socket ID to every axios request so that Laravel's
 * broadcast()->toOthers() can correctly identify and exclude the sender's
 * WebSocket connection from receiving the event.
 */
window.axios.interceptors.request.use((config) => {
    const socketId = window.Echo?.socketId();
    if (socketId) {
        config.headers['X-Socket-ID'] = socketId;
    }
    return config;
});
