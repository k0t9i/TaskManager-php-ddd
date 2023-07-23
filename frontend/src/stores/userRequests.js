import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";
import {useQueryStore} from "./queryStore";

const STORE_ID = 'userRequests';

export const useUserRequestsStore = defineStore({
    id: STORE_ID,
    state: () => ({
        requests: [],
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
        async load() {
            this.error = '';
            this.loading = true;
            const cache = useCacheStore();
            const queryStore = useQueryStore();

            return cache.request(
                STORE_ID + queryStore.getHash,
                () => axiosInstance
                    .get('/users/requests/', {
                        params: queryStore.getParams
                    })
                    .then((response) => {
                        this.requests = [];
                        for (const [key, value] of Object.entries(response.data.items)) {
                            let val = value;
                            val.changeDate = new Date(value.changeDate);
                            this.requests.push(val);
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