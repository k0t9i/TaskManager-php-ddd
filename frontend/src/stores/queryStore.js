import {defineStore} from "pinia";
import {useRoute} from "vue-router";
import {watch} from "vue";

export const useQueryStore = defineStore({
    id: 'queryStore',
    state: () => ({
        filters: {},
        sorts: {}
    }),
    getters: {
        getFilters: (state) => {
            const route = useRoute();
            return state.filters[route.path]
        },
        getSorts: (state) => {
            const route = useRoute();
            return state.sorts[route.path]
        },
        getHash: (state) => {
            const route = useRoute();
            let filterHash = state.filters[route.path] !== undefined ?
                JSON.stringify(state.filters[route.path]) :
                '';
            let sortHash = state.sorts[route.path] !== undefined ?
                JSON.stringify(state.sorts[route.path]) :
                '';
            return filterHash + sortHash;
        }
    },
    actions: {
        watch() {
            const route = useRoute();
            let _this = this;

            watch(
                () => route.query,
                function (query) {
                    if (query.filter !== undefined) {
                        let filter = query.filter;
                        if (!Array.isArray(filter)) {
                            filter = [filter];
                        }
                        filter.sort();

                        let queryFilters = {};
                        for (const item of filter) {
                            let parts = item.split('|');
                            queryFilters[parts.shift()] = parts.join('|');
                        }

                        _this.filters[route.path] = queryFilters;
                    }
                    if (query.sort !== undefined) {
                        let sort = query.sort;
                        if (!Array.isArray(sort)) {
                            sort = [sort];
                        }
                        sort.sort();

                        _this.sorts[route.path] = sort;
                    }
                }
            );
        }
    }
});