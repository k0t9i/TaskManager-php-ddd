import { createApp } from 'vue';
import { createPinia } from 'pinia';
import './styles/main.scss';
import App from './App.vue';
import router from "./router";
import loader from './components/Loader.vue'

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.component('Loader', loader);
app.provide('loader', loader);

app.mount('#app');
