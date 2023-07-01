import { createRouter, createWebHistory } from 'vue-router';
import MainView from '../views/MainView.vue';
import routes from './routes';
import { useAuthStore } from "../stores/auth";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: routes.main.uri,
            name: 'main',
            component: MainView
        },
        {
            path: routes.login.uri,
            name: 'login',
            component: () => import('../views/LoginView.vue')
        },
        {
            path: routes.register.uri,
            name: 'register',
            component: () => import('../views/RegisterView.vue')
        }
    ]
});

router.beforeEach(async (to) => {
    let isPublic = true;
    Object.values(routes).forEach(value => {
        if (value.uri === to.path && !value.isPublic) {
            isPublic = false;
        }
    });

    const authStore = useAuthStore();

    if (!isPublic && !authStore.token) {
        return routes.login.uri;
    }
});

export default router;