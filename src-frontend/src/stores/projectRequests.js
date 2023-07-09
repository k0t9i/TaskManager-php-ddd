import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";

export const useProjectRequestsStore = defineStore({
    id: 'projectRequests',
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
        },
        countAll: (state) => {
            return (projectId) => Object.entries(state.requests[projectId] ?? {}).length;
        },
        countPending: (state) => {
            return (projectId) => Object.entries(state.requests[projectId] ?? {}).filter(([key, value]) => value.status === 0).length;
        }
    },
    actions: {
        async load(projectId) {
            this.errors[projectId] = '';
            return axiosInstance.get(`/projects/${projectId}/requests/`)
                .then((response) => {
                    if (!this.requests[projectId]) {
                        this.requests[projectId] = {};
                    }
                    for (const [key, value] of Object.entries(response.data)) {
                        this.requests[projectId][value.id] = value;
                        this.requests[projectId][value.id].changeDate = new Date(value.changeDate);
                    }
                })
                .catch((error) => {
                    this.errors[projectId] = error.response.data.message;
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