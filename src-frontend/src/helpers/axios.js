import axios from "axios";
import {useAuthStore} from "../stores/auth";

const axiosInstance = axios.create({
    baseURL: import.meta.env.VITE_API_URL
});

axiosInstance.interceptors.request.use((request) => {
    const authStore = useAuthStore();

    if (authStore.token) {
        request.headers.Authorization = 'Bearer ' + authStore.token;
    }

    return request;
});

axiosInstance.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response.status === 401) {
        const authStore = useAuthStore();
        authStore.logout();
    } else {
        return Promise.reject(error);
    }
});

export default axiosInstance;