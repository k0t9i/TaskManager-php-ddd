import {defineStore} from 'pinia';
import router from "../router";
import routes from "../router/routes";
import axiosInstance from "../helpers/axios";

export const useAuthStore = defineStore({
    id: 'auth',
    state: () => ({
        token: localStorage.getItem('token')
    }),
    actions: {
        async login(email, password) {
            return axiosInstance.post('/security/login/', {
                email: email,
                password: password
            }).then((response) => {
                localStorage.setItem('token', response.data.token);
                this.token = localStorage.getItem('token');

                router.push(routes.main.uri);

                return response;
            });
        },
        logout() {
            router.push(routes.login.uri).then((result) => {
                localStorage.removeItem('token');
                this.token = localStorage.getItem('token');
                return result;
            });
        }
    }
});