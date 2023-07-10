import {defineStore} from 'pinia';
import axiosInstance from "../helpers/axios";

export const useUserStore = defineStore({
    id: 'user',
    state: () => ({
        user: {},
        error: '',
        locked: false
    }),
    actions: {
        async load() {
            this.error = '';
            return axiosInstance
                .get(`/users/`).then((response) => {
                    this.user.email = response.data.email;
                    this.user.firstname = response.data.firstname;
                    this.user.lastname = response.data.lastname;
                    return response;
                })
                .catch((error) => {
                    this.error = error.response.data.message;
                });
        },
        async save(){
            this.error = '';
            this.locked = true;
            return axiosInstance
                .patch(`/users/`, this.user)
                .catch((error) => {
                    this.error = error.response.data.message;
                })
                .finally(() => {
                    this.locked = false;
                });
        }
    }
});