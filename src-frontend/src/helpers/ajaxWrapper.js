import axios from "axios";
import loader from '../components/loader'
import {useAuthStore} from "../stores/auth";

const ajaxWrapper = {
    get:  async (url, config) => {
        return wrap(
            axios.get(url, injectAuthHeader(config))
        );
    },
    post: async (url, data, config) => {
        return wrap(
            axios.post(url, data, injectAuthHeader(config))
        );
    },
    put: async (url, data, config) => {
        return wrap(
            axios.put(url, data, injectAuthHeader(config))
        );
    },
    patch: async (url, data, config) => {
        return wrap(
            axios.patch(url, data, injectAuthHeader(config))
        );
    },
    delete: async (url, config) => {
        return wrap(
            axios.delete(url, injectAuthHeader(config))
        );
    }
};

async function wrap(promise) {
    return handleLoader(promise)
        .catch(handleException);
}

function injectAuthHeader(config) {
    const authStore = useAuthStore();

    config = config || {};

    if (authStore.token) {
        const headers = config.headers || {};
        headers.Authorization = 'Bearer ' + authStore.token;
        config.headers = headers;
    }

    return config;
}

async function handleLoader(promise) {
    loader.show();
    return promise.finally(() => {
        loader.hide();
    });
}

function handleException(e) {
    if (e.response.status === 401) {
        const authStore = useAuthStore();
        authStore.logout();
    } else {
        throw e;
    }
}

export default ajaxWrapper;