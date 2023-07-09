import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";

export const useTaskStore = defineStore({
    id: 'task',
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
            return axiosInstance.get(`/tasks/${id}/`)
                .then((response) => {
                    if (this.tasks[id]) {
                        for (const [key, value] of Object.entries(response.data)) {
                            this.tasks[value.id][key] = value;
                        }
                    } else {
                        this.tasks[id] = response.data
                    }
                    this.tasks[id].startDate = new Date(this.tasks[id].startDate);
                    this.tasks[id].finishDate = new Date(this.tasks[id].finishDate);
                })
                .catch((error) => {
                    this.errors[id] = error.response.data.message;
                });
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