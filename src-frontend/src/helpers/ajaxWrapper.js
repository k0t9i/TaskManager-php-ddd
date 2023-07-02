import axios from "axios";
import loader from '../components/loader'

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
};

function wrap(promise) {
    return handleLoader(promise);
}

function handleLoader(promise) {
    loader.show();
    return promise.finally(() => {
        loader.hide();
    });
}

export default ajaxWrapper;