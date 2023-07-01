import axios from "axios";
import {useLoaderStore} from "../stores/loader";

const ajaxWrapper = {
    get: (url, config) => {
        return wrap(axios.get(url, config));
    },
    post: (url, data, config) => {
        return wrap(axios.post(url, data, config));
    },
    put: (url, data, config) => {
        return wrap(axios.put(url, data, config));
    },
    patch: (url, data, config) => {
        return wrap(axios.patch(url, data, config));
    },
    delete: (url, config) => {
        return wrap(axios.delete(url, config));
    }
}

function wrap(promise) {
    const loaderStore = useLoaderStore();

    loaderStore.isLoading = true;
    promise.finally(() => {
        loaderStore.isLoading = false;
    })
    return promise;
}

export default ajaxWrapper;