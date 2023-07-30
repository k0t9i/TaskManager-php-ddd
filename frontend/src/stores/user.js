import {defineStore} from 'pinia';
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";

const STORE_ID = 'user';

export const useUserStore = defineStore({
    id: STORE_ID,
    state: () => ({
        user: {},
        error: '',
        locked: false
    }),
    actions: {
        async load() {
            this.error = '';
            const cache = useCacheStore();

            return cache.request(
                STORE_ID,
                () => axiosInstance
                    .get(`/users/`).then((response) => {
                        this.user.id = response.data.id;
                        this.user.email = response.data.email;
                        this.user.firstname = response.data.firstname;
                        this.user.lastname = response.data.lastname;
                        this.user.version = response.data.version;
                        return response;
                    })
                    .catch((error) => {
                        this.error = error.response.data.message;
                        throw error;
                    })
            );
        },
        async save(){
            this.error = '';
            this.locked = true;
            return axiosInstance
                .patch(`/users/`, this.user)
                .then((response) => {
                    this.user.version = response.data.version;
                    return response;
                })
                .catch((error) => {
                    this.error = error.response.data.message;
                })
                .finally(() => {
                    this.locked = false;
                });
        }
    }
});