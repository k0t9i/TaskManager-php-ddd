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
            const response = await ajaxWrapper.post(`${baseUrl}/login/`, {
                email: email,
                password: password
            });

            this.token = response.data.token;
            localStorage.setItem('token', this.token);

            await router.push(routes.main.uri)
        }
    }
});