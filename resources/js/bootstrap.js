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

// リクエストインターセプター：すべてのリクエストに認証トークンを追加
window.axios.interceptors.request.use(
    config => {
        const authToken = localStorage.getItem('token');
        console.log('Request interceptor - Token from localStorage:', authToken ? `${authToken.substring(0, 10)}...` : 'none');
        if (authToken) {
            config.headers.Authorization = `Bearer ${authToken}`;
            console.log('Request interceptor - Authorization header set:', config.headers.Authorization ? 'yes' : 'no');
        }
        console.log('Request URL:', config.url);
        console.log('Request headers:', config.headers);
        return config;
    },
    error => {
        return Promise.reject(error);
    }
);

// エラーハンドリング
window.axios.interceptors.response.use(
    response => response,
    error => {
        console.error('Axios error:', error.response || error);
        return Promise.reject(error);
    }
);
