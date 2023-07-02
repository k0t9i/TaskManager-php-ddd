import {defineStore} from 'pinia';
import ajaxWrapper from "../helpers/ajaxWrapper";
import router from "../router";
import routes from "../router/routes";

export const useAuthStore = defineStore({
    id: 'auth',
    state: () => ({
        token: localStorage.getItem('token')
    }),
    actions: {
        async login(email, password) {
            return ajaxWrapper.post(`${import.meta.env.VITE_API_URL}/security/login/`, {
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
            localStorage.removeItem('token');
            this.token = localStorage.getItem('token');
            router.push(routes.login.uri);
        }
    }
});