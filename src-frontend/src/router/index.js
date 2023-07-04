import {createRouter, createWebHistory} from 'vue-router';
import routes from './routes';
import {useAuthStore} from "../stores/auth";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    linkActiveClass: 'active',
    routes: [
        {
            path: routes.main.uri,
            component: () => import('../views/MainView.vue')
        },
        {
            path: routes.profile.uri,
            component: () => import('../views/ProfileView.vue')
        },
        {
            path: routes.create_project.uri,
            component: () => import('../views/CreateProjectView.vue')
        },
        {
            path: routes.login.uri,
            component: () => import('../views/LoginView.vue')
        },
        {
            path: routes.register.uri,
            component: () => import('../views/RegisterView.vue')
        }
    ]
});

router.beforeEach(async (to) => {
    let isPublic = true;
    Object.values(routes).forEach(value => {
        const isPublicUri = value.isPublic !== undefined ? value.isPublic : false;
        if (value.uri === to.path && !isPublicUri) {
            isPublic = false;
        }
    });

    const authStore = useAuthStore();

    if (!isPublic && !authStore.token) {
        return routes.login.uri;
    }

    if (isPublic && authStore.token) {
        return routes.main.uri;
    }
});

export default router;