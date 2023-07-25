import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";
import {useQueryStore} from "./queryStore";

const STORE_ID = 'projectRequests';

export const useProjectRequestsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        requests: {},
        errors: {},
        locked: {},
        pagination: {},
        loading: {}
    }),
    getters: {
        getRequests: (state) => {
            return (projectId) => state.requests[projectId] ?? {};
        },
        error: (state) => {
            return (projectId) => state.errors[projectId] ?? '';
        },
        isLocked: (state) => {
            return (id) => state.locked[id] ?? false;
        },
        isLoading: (state) => {
            return (projectId) => state.loading[projectId] ?? false;
        },
        getPaginationMetadata: (state) => {
            return (projectId) => state.pagination[projectId] ?? {};
        }
    },
    actions: {
        async load(projectId) {
            this.errors[projectId] = '';
            this.loading[projectId] = true;
            const cache = useCacheStore();
            const queryStore = useQueryStore();

            return cache.request(
                STORE_ID + projectId + queryStore.getHash,
                () => axiosInstance
                    .get(`/projects/${projectId}/requests/`, {
                        params: queryStore.getParams
                    })
                    .then((response) => {
                        this.requests[projectId] = {};
                        for (const [key, value] of Object.entries(response.data.items)) {
                            this.requests[projectId][value.id] = value;
                            this.requests[projectId][value.id].changeDate = new Date(value.changeDate);
                        }

                        this.pagination[projectId] = response.data.page;

                        return response;
                    })
                    .catch((error) => {
                        this.errors[projectId] = error.response.data.message;
                        throw error;
                    })
                )
                .finally(() => {
                    this.loading[projectId] = false;
                });
        },
        async confirm(projectId, id) {
            return this.changeStatus(projectId, id, 'confirm', 1);
        },
        async reject(projectId, id) {
            return this.changeStatus(projectId, id, 'reject', 2);
        },
        async changeStatus(projectId, id, endpoint, targetStatus) {
            this.errors[projectId] = '';
            this.locked[id] = true;
            return axiosInstance.patch(`/projects/${projectId}/requests/${id}/${endpoint}/`)
                .then((response) => {
                    this.requests[projectId][id].status = targetStatus;
                    return response;
                })
                .catch((error) => {
                    this.errors[projectId] = error.response.data.message;
                })
                .finally(() => {
                    this.locked[id] = false;
                });
        }
    }
});