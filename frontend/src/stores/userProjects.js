import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";
import {useQueryStore} from "./queryStore";

const STORE_ID = 'userProjects';

export const useUserProjectsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        projects: {},
        error: '',
        pagination: {},
        loading: false
    }),
    getters: {
        isLoading: (state) => {
            return state.loading;
        },
        getPaginationMetadata: (state) => {
            return state.pagination;
        }
    },
    actions: {
        async create(project) {
            this.error = '';
            return axiosInstance.post('/projects/', project)
                .then((response) => {
                    const id = response.data.id;
                    project.id = id;
                    this.projects[id] = {};
                    for (const [key, value] of Object.entries(project)) {
                        this.projects[id][key] = value;
                    }
                    this.projects[id].finishDate = new Date(this.projects[id].finishDate);
                })
                .catch((error) => {
                    this.error = error.response.data.message;
                });
        },
        async load() {
            this.error = '';
            this.loading = true;
            const cache = useCacheStore();
            const queryStore = useQueryStore();

            return cache.request(
                STORE_ID + queryStore.getHash,
                () => axiosInstance
                    .get('/users/projects/', {
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
                )
                .finally(() => {
                    this.loading = false;
                });
        }
    }
});