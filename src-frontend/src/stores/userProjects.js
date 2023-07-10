import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";

const STORE_ID = 'userProjects';

export const useUserProjectsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        projects: {},
        error: ''
    }),
    getters: {
        countAll: (state) => Object.entries(state.projects).length
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
            const cache = useCacheStore();

            return cache.request(
                STORE_ID,
                axiosInstance
                    .get('/users/projects/')
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
        }
    }
});