import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";

const STORE_ID = 'availableProjects';

export const useAvailableProjectsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        projects: {},
        error: '',
        locked: {}
    }),
    getters: {
        isLocked: (state) => {
            return (id) => state.locked[id];
        }
    },
    actions: {
        async load() {
            this.error = '';
            const cache = useCacheStore();

            return cache.request(
                STORE_ID,
                () => axiosInstance
                    .get('/projects/')
                    .then((response) => {
                        for (const [key, value] of Object.entries(response.data)) {
                            this.projects[value.id] = value;
                            this.projects[value.id].finishDate = new Date(value.finishDate);
                        }
                        return response;
                    })
                    .catch((error) => {
                        this.error = error.response.data.message;
                        throw error;
                    })
            );
        },
        async join(id) {
            this.error = '';
            this.locked[id] = true;

            return await axiosInstance.post(`/projects/${id}/requests/`)
                .then((response) => {
                    this.projects[id].lastRequestStatus = 0;
                    return response;
                })
                .catch((error) => {
                    this.error = error.response.data.message;
                })
                .finally(() => {
                    this.locked[id] = false;
                });
        }
    }
});