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
        locked: {}
    }),
    getters: {
        getRequests: (state) => {
            return (projectId) => state.requests[projectId];
        },
        error: (state) => {
            return (projectId) => state.errors[projectId];
        },
        isLocked: (state) => {
            return (id) => state.locked[id];
        }
    },
    actions: {
        async load(projectId) {
            this.errors[projectId] = '';
            const cache = useCacheStore();
            const queryStore = useQueryStore();

            return cache.request(
                STORE_ID + projectId + queryStore.getHash,
                () => axiosInstance
                    .get(`/projects/${projectId}/requests/`, {
                        params: queryStore.getParams
                    })
                    .then((response) => {
                        if (!this.requests[projectId]) {
                            this.requests[projectId] = {};
                        }
                        for (const [key, value] of Object.entries(response.data.items)) {
                            this.requests[projectId][value.id] = value;
                            this.requests[projectId][value.id].changeDate = new Date(value.changeDate);
                        }
                        return response;
                    })
                    .catch((error) => {
                        this.errors[projectId] = error.response.data.message;
                        throw error;
                    })
            );
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