import {createRouter, createWebHistory} from 'vue-router';
import {useAuthStore} from "../stores/auth";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    linkActiveClass: 'active',
    routes: [
        {
            path: '/',
            name: 'main',
            component: () => import('../views/MainView.vue')
        },
        {
            path: '/profile',
            name: 'profile',
            component: () => import('../views/ProfileView.vue')
        },
        {
            path: '/create-project',
            name: 'create_project',
            component: () => import('../views/CreateProjectView.vue')
        },
        {
            path: '/project-info/:id',
            name: 'project_info',
            component: () => import('../views/ProjectInfoView.vue'),
            redirect: {name: 'edit_project'},
            children: [
                {
                    path: 'edit',
                    name: 'edit_project',
                    component: () => import('../views//EditProjectView.vue')
                },
                {
                    path: 'requests',
                    name: 'project_requests',
                    component: () => import('../views//ProjectRequests.vue')
                }
            ]
        },
        {
            path: '/user-requests/',
            name: 'user_requests',
            component: () => import('../views/UserRequests.vue'),
        },
        {
            path: '/user-projects',
            name: 'user_projects',
            component: () => import('../views/UserProjects.vue')
        },
        {
            path: '/login',
            name: 'login',
            component: () => import('../views/LoginView.vue'),
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('../views/RegisterView.vue')
        }
    ]
});

router.beforeEach(async (to) => {
    const publicPages = ['login', 'register'];
    const isPublic = publicPages.includes(to.name);
    const authStore = useAuthStore();

    if (!isPublic && !authStore.token) {
        return {name: 'login'};
    }

    if (isPublic && authStore.token) {
        return {name: 'main'};
    }
});

export default router;