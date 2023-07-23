import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";
import {useQueryStore} from "./queryStore";

const STORE_ID = 'availableProjects';

export const useAvailableProjectsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        projects: {},
        error: '',
        locked: {},
        pagination: {},
        loading: false
    }),
    getters: {
        isLocked: (state) => {
            return (id) => state.locked[id];
        },
        isLoading: (state) => {
            return state.loading;
        },
        getPaginationMetadata: (state) => {
            return state.pagination;
        }
    },
    actions: {
        async load() {
            this.error = '';
            this.loading = true;
            const cache = useCacheStore();
            const queryStore = useQueryStore();

            return cache.request(
                STORE_ID + queryStore.getHash,
                () => axiosInstance
                    .get('/projects/', {
                        params: queryStore.getParams
                    })
                    .then((response) => {
                        this.projects = {};
                        for (const [key, value] of Object.entries(response.data.items)) {
                            this.projects[value.id] = value;
                            this.projects[value.id].finishDate = new Date(value.finishDate);
                        }

                        this.pagination = response.data.page;

                        return response;
                    })
                    .catch((error) => {
                        this.error = error.response.data.message;
                        throw error;
                    })
                    .finally(() => {
                        this.loading = false;
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