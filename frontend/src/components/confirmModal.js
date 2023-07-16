import {Modal} from "bootstrap";
import {reactive} from "vue";

let modal;
let confirmCallback;

const titleDefault = 'Are you sure?';
const bodyDefault = 'Are you sure?';
const config = reactive({
    title: titleDefault,
    body: bodyDefault
})

export default {
    init: () => {
        if (modal) {
            modal.dispose();
        }
        modal = new Modal(document.getElementById('confirmModal'));
    },
    show: (confirm, body = undefined, title = undefined) => {
        confirmCallback = confirm;
        if (title !== undefined) {
            config.title = title;
        } else {
            config.title = titleDefault;
        }
        if (body !== undefined) {
            config.body = body;
        } else {
            config.body = bodyDefault;
        }
        modal.show();
    },
    confirm: () => {
        confirmCallback.apply();
        modal.hide();
    },
    getConfig: () => {
        return config;
    }
};