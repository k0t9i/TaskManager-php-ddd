import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";

const STORE_ID = 'task';

export const useTaskStore = defineStore({
    id: STORE_ID,
    state: () => ({
        tasks: {},
        errors: {},
        locked: {}
    }),
    getters: {
        task: (state) => {
            return (id) => state.tasks[id];
        },
        error: (state) => {
            return (id) => state.errors[id];
        },
        isLocked: (state) => {
            return (id) => state.locked[id];
        }
    },
    actions: {
        async load(id) {
            this.errors[id] = '';
            const cache = useCacheStore();

            return cache.request(
                STORE_ID + ':' + id,
                () => axiosInstance
                    .get(`/tasks/${id}/`)
                    .then((response) => {
                        if (this.tasks[id]) {
                            for (const [key, value] of Object.entries(response.data)) {
                                this.tasks[id][key] = value;
                            }
                        } else {
                            this.tasks[id] = response.data
                        }
                        this.tasks[id].startDate = new Date(this.tasks[id].startDate);
                        this.tasks[id].finishDate = new Date(this.tasks[id].finishDate);
                        return response;
                    })
                    .catch((error) => {
                        this.errors[id] = error.response.data.message;
                        throw error;
                    })
            );
        },
        async toggleStatus(id) {
            this.errors[id] = '';
            this.locked[id] = true;
            const endpoint = this.tasks[id].status === 1 ? 'close' : 'activate';
            return axiosInstance.patch(`/tasks/${id}/${endpoint}/`)
                .then((response) => {
                    this.tasks[id].status = Math.abs(this.tasks[id].status - 1);
                    return response;
                })
                .catch((error) => {
                    this.errors[id] = error.response.data.message;
                })
                .finally(() => {
                    this.locked[id] = false;
                });
        },
        async update(id){
            this.errors[id] = '';
            this.locked[id] = true;
            return axiosInstance
                .patch(`/tasks/${id}/`, this.tasks[id])
                .catch((error) => {
                    this.errors[id] = error.response.data.message;
                })
                .finally(() => {
                    this.locked[id] = false;
                });
        },
    }
});