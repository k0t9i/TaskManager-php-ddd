import { defineStore } from 'pinia';
import ajaxWrapper from "../helpers/ajaxWrapper";
import router from "../router";
import routes from "../router/routes";

const baseUrl = `${import.meta.env.VITE_API_URL}/security`;

export const useAuthStore = defineStore({
    id: 'auth',
    state: () => ({
        token: localStorage.getItem('token')
    }),
    actions: {
        async login(email, password) {
            return ajaxWrapper.post(`${baseUrl}/login/`, {
                email: email,
                password: password
            }).then((response) => {
                localStorage.setItem('token', response.data.token);
                this.token = localStorage.getItem('token');

                router.push(routes.main.uri);
            });
        },
        logout() {
            localStorage.removeItem('token');
            this.token = localStorage.getItem('token');
            router.push(routes.login.uri);
        }
    }
});