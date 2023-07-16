import {defineStore} from 'pinia';

const DEFAULT_LIFETIME = 2000;

export const useCacheStore = defineStore({
    id: 'cache',
    state: () => ({
        lastSuccessRequests: {},
        promises: {}
    }),
    actions: {
        async request(id, promise, lifetime = undefined) {
            if (this.lastSuccessRequests[id]) {
                const delta = Date.now() - this.lastSuccessRequests[id];
                if (delta < (lifetime ?? DEFAULT_LIFETIME)) {
                    return new Promise((resolve) => {
                        resolve();
                    });
                }
            }

            if (this.promises[id]) {
                return this.promises[id];
            }
            this.promises[id] = promise;

            return promise
                .then((response) => {
                    this.lastSuccessRequests[id] = Date.now();
                    return response;
                })
                .catch((error) => {
                    this.lastSuccessRequests[id] = null;
                })
                .finally(() => {
                    this.promises[id] = null;
                })
        }
    }
})