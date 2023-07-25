import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";
import {useQueryStore} from "./queryStore";

const STORE_ID = 'projectParticipants';

export const useProjectParticipantsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        participants: {},
        errors: {},
        locked: {},
        pagination: {},
        loading: {}
    }),
    getters: {
        getParticipants: (state) => {
            return (projectId) => state.participants[projectId] ?? {};
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
                    .get(`/projects/${projectId}/participants/`, {
                        params: queryStore.getParams
                    })
                    .then((response) => {
                        this.participants[projectId] = {};
                        for (const [key, value] of Object.entries(response.data.items)) {
                            this.participants[projectId][value.userId] = value;
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