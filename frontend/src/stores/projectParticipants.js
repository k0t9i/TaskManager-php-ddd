import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";

const STORE_ID = 'projectParticipants';

export const useProjectParticipantsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        participants: {},
        errors: {},
        locked: {}
    }),
    getters: {
        getParticipants: (state) => {
            return (projectId) => state.participants[projectId];
        },
        error: (state) => {
            return (projectId) => state.errors[projectId];
        },
        isLocked: (state) => {
            return (id) => state.locked[id];
        },
        countAll: (state) => {
            return (projectId) => Object.entries(state.participants[projectId] ?? {}).length;
        }
    },
    actions: {
        async load(projectId) {
            this.errors[projectId] = '';
            const cache = useCacheStore();

            return cache.request(
                STORE_ID + ':' + projectId,
                () => axiosInstance
                    .get(`/projects/${projectId}/participants/`)
                    .then((response) => {
                        this.participants[projectId] = {};
                        for (const [key, value] of Object.entries(response.data)) {
                            this.participants[projectId][value.userId] = value;
                        }
                        return response;
                    })
                    .catch((error) => {
                        this.errors[projectId] = error.response.data.message;
                        throw error;
                    })
            );
        },
        async remove(projectId, id) {
            this.errors[projectId] = '';
            this.locked[id] = true;

            return axiosInstance.delete(`/projects/${projectId}/participants/${id}/`)
                .then((response) => {
                    delete this.participants[projectId][id];
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