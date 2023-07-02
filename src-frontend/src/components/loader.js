import {ref} from "vue";

const isShowing = ref(false);

const loader = {
    show: () => {
        isShowing.value = true
    },
    hide: () => {
        isShowing.value = false
    },
    isShowing: () => isShowing.value
};

export default loader;