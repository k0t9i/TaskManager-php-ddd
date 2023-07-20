import {defineStore} from 'pinia';
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";

const STORE_ID = 'project';

export const useProjectStore = defineStore({
    id: STORE_ID,
    state: () => ({
        projects: {},
        errors: {},
        locked: {}
    }),
    getters: {
        project: (state) => {
            return (id) => state.projects[id];
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
                STORE_ID + id,
                () => axiosInstance
                    .get(`/projects/${id}/`)
                    .then((response) => {
                        if (this.projects[id]) {
                            for (const [key, value] of Object.entries(response.data)) {
                                this.projects[id][key] = response.data[key];
                            }
                        } else {
                            this.projects[id] = response.data
                        }
                        this.projects[id].finishDate = new Date(this.projects[id].finishDate);
                        return response;
                    })
                    .catch((error) => {
                        this.errors[id] = error.response.data.message;
                        throw error;
                    })
            );
        },
        async save(id){
            this.errors[id] = '';
            this.locked[id] = true;
            return axiosInstance
                .patch(`/projects/${id}/`, this.projects[id])
                .catch((error) => {
                    this.errors[id] = error.response.data.message;
                })
                .finally(() => {
                    this.locked[id] = false;
                });
        },
        async toggleStatus(id) {
            this.errors[id] = '';
            this.locked[id] = true;
            const endpoint = this.projects[id].status === 1 ? 'close' : 'activate';
            return axiosInstance.patch(`/projects/${id}/${endpoint}/`)
                .then((response) => {
                    this.projects[id].status = Math.abs(this.projects[id].status - 1);
                    return response;
                })
                .catch((error) => {
                    this.errors[id] = error.response.data.message;
                })
                .finally(() => {
                    this.locked[id] = false;
                });
        },
        async makeOwner(id, newOwnerId) {
            this.errors[id] = '';
            this.locked[id] = true;

            return axiosInstance.patch(`/projects/${id}/change-owner/${newOwnerId}/`)
                .then((response) => {
                    this.projects[id].isOwner = false;
                    return response;
                })
                .catch((error) => {
                    this.errors[id] = error.response.data.message;
                })
                .finally(() => {
                    this.locked[id] = false;
                });
        }
    }
});