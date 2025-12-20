/**
 * API Client & Auth Handler
 * Originally app.js, renamed to avoid conflict with Velzon's app.js
 */
const API = {
    // Dynamically calculate Base URL relative to current location
    baseUrl: (() => {
        const path = window.location.pathname;
        const publicIndex = path.indexOf('/public');
        
        if (publicIndex !== -1) {
            return window.location.origin + path.substring(0, publicIndex) + '/public';
        }
        return window.location.origin;
    })(),

    // Helper to make authenticated requests
    async fetch(url, options = {}) {
        const endpoint = url.startsWith('/') ? url.substring(1) : url;
        const fullUrl = `${this.baseUrl}/${endpoint}`; 
        
        const token = localStorage.getItem('accessToken');

        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
        
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken, 
            ...options.headers
        };

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const config = {
            ...options,
            headers
        };

        const response = await fetch(fullUrl, config);
        
        // Handle Token Expiry
        if (response.status === 401) {
            if (!window.location.pathname.includes('/login') && !window.location.pathname.includes('/register')) {
                // Optional: Use SweetAlert if available, else alert
                if(typeof Swal !== 'undefined') {
                    Swal.fire({icon: 'error', title: 'Session Expired', text: 'Please login again'}).then(() => API.logout());
                } else {
                    alert('Session expired. Please login again.');
                    API.logout();
                }
            }
        }

        return response;
    },

    saveTokens(tokens) {
        localStorage.setItem('accessToken', tokens.access.token);
        localStorage.setItem('refreshToken', tokens.refresh.token);
    },

    logout() {
        localStorage.removeItem('accessToken');
        localStorage.removeItem('refreshToken');
        window.location.href = `${this.baseUrl}/login`;
    },

    checkAuth() {
        const token = localStorage.getItem('accessToken');
        const path = window.location.pathname;
        
        // Allowed public paths
        const publicPaths = ['/login', '/register', '/forgot-password'];
        const isPublic = publicPaths.some(p => path.includes(p));

        if (!token && !isPublic) {
            window.location.href = `${this.baseUrl}/login`;
        }
    },
    
    // Helper to extract payload from JWT
    getUser() {
        const token = localStorage.getItem('accessToken');
        if(!token) return null;
        try {
            return JSON.parse(atob(token.split('.')[1]));
        } catch (e) {
            return null;
        }
    }
};

// Run Auth Check on Load
document.addEventListener('DOMContentLoaded', () => {
    API.checkAuth();
});