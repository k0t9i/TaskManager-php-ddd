import {createRouter, createWebHistory} from 'vue-router';
import {useAuthStore} from "../stores/auth";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    linkActiveClass: 'active',
    routes: [
        {
            path: '/',
            component: () => import('../views/MainView.vue'),
            children: [
                {
                    path: '',
                    name: 'main',
                    component: () => import('../components/AsyncAvailableProjectsList.vue')
                }
            ]
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
            path: '/project/:id',
            name: 'project',
            component: () => import('../views/ProjectView.vue'),
            redirect: {name: 'project_info'},
            children: [
                {
                    path: 'info',
                    name: 'project_info',
                    component: () => import('../views/ProjectInfoView.vue')
                },
                {
                    path: 'requests',
                    component: () => import('../views/ProjectRequests.vue'),
                    children: [
                        {
                            path: '',
                            name: 'project_requests',
                            component: () => import('../components/AsyncProjectRequestList.vue')
                        }
                    ]
                },
                {
                    path: 'tasks',
                    component: () => import('../views/ProjectTasks.vue'),
                    children: [
                        {
                            path: '',
                            name: 'project_tasks',
                            component: () => import('../components/AsyncProjectTaskList.vue')
                        }
                    ]
                },
                {
                    path: 'tasks/create',
                    name: 'create_task',
                    component: () => import('../views/CreateTaskView.vue')
                },
                {
                    path: 'tasks/:taskId',
                    name: 'edit_task',
                    component: () => import('../views/EditTaskView.vue'),
                    redirect: {name: 'task_info'},
                    children: [
                        {
                            path: 'info',
                            name: 'task_info',
                            component: () => import('../components/AsyncEditTask.vue')
                        },
                        {
                            path: 'links',
                            component: () => import('../views/TaskLinksView.vue'),
                            children: [
                                {
                                    path: '',
                                    name: 'task_links',
                                    component: () => import('../components/AsyncTaskLinks.vue')
                                }
                            ]
                        }
                    ]
                },
                {
                    path: 'participants',
                    component: () => import('../views/ProjectParticipants.vue'),
                    children: [
                        {
                            path: '',
                            name: 'project_participants',
                            component: () => import('../components/AsyncProjectParticipantsList.vue')
                        }
                    ]
                },
            ]
        },
        {
            path: '/user-requests/',
            component: () => import('../views/UserRequests.vue'),
            children: [
                {
                    path: '',
                    name: 'user_requests',
                    component: () => import('../components/AsyncUserRequestList.vue')
                }
            ]
        },
        {
            path: '/user-projects',
            component: () => import('../views/UserProjects.vue'),
            children: [
                {
                    path: '',
                    name: 'user_projects',
                    component: () => import('../components/AsyncUserProjectList.vue')
                }
            ]
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