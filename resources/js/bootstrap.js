import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';

// CSRF トークンの設定
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// エラーハンドリング
window.axios.interceptors.response.use(
    response => response,
    error => {
        console.error('Axios error:', error.response || error);
        return Promise.reject(error);
    }
);
