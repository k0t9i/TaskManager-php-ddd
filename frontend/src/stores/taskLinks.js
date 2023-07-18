import {defineStore} from "pinia";
import axiosInstance from "../helpers/axios";
import {useCacheStore} from "./cache";
import {useTasksStore} from "./tasks";

const STORE_ID = 'taskLinks';
const tasksStore = useTasksStore();

export const useTaskLinksStore = defineStore({
    id: STORE_ID,
    state: () => ({
        links: {},
        errors: {},
        locked: {},
        availableTasks: {}
    }),
    getters: {
        getLinks: (state) => {
            return (taskId) => state.links[taskId];
        },
        error: (state) => {
            return (taskId) => state.errors[taskId];
        },
        isLocked: (state) => {
            return (id) => state.locked[id];
        },
        getAvailableTasks: (state) => {
            return (taskId) => state.availableTasks[taskId];
        },
    },
    actions: {
        async create(taskId, linkedTaskId) {
            this.errors[taskId] = '';
            return axiosInstance.post(`/tasks/${taskId}/links/${linkedTaskId}/`)
                .then((response) => {
                    if (!this.links[taskId]) {
                        this.links[taskId] = {};
                    }
                    const linkedTask = this.availableTasks[taskId][linkedTaskId];
                    this.links[taskId][linkedTaskId] = {
                        taskId: taskId,
                        linkedTaskId: linkedTask.id,
                        linkedTaskName: linkedTask.name,
                        linkedTaskStatus: linkedTask.status
                    };
                    delete this.availableTasks[taskId][linkedTaskId];

                    return response;
                })
                .catch((error) => {
                    this.errors[taskId] = error.response.data.message;
                });
        },
        async load(projectId, taskId) {
            this.errors[taskId] = '';
            const cache = useCacheStore();

            return cache.request(
                STORE_ID + ':' + taskId,
                () => axiosInstance
                    .get(`/tasks/${taskId}/links/`)
                    .then((response) => {
                        if (!this.links[taskId]) {
                            this.links[taskId] = {};
                        }
                        for (const [key, value] of Object.entries(response.data)) {
                            this.links[taskId][value.linkedTaskId] = value;
                        }

                        return tasksStore.load(projectId)
                            .then((response) => {
                                this.computeAvailableTasks(projectId, taskId)

                                return response;
                            });
                    })
                    .catch((error) => {
                        this.errors[taskId] = error.response.data.message;
                        throw error;
                    })
            );
        },
        async remove(projectId, taskId, linkedTaskId) {
            this.errors[taskId] = '';
            this.locked[linkedTaskId] = true;
            return axiosInstance.delete(`/tasks/${taskId}/links/${linkedTaskId}/`)
                .then((response) => {
                    const allTasks = tasksStore.getTasks(projectId);
                    if (!this.availableTasks[taskId]) {
                        this.availableTasks[taskId] = {};
                    }
                    this.availableTasks[taskId][linkedTaskId] = allTasks[linkedTaskId];
                    delete this.links[taskId][linkedTaskId];

                    return response;
                })
                .catch((error) => {
                    this.errors[taskId] = error.response.data.message;
                })
                .finally(() => {
                    this.locked[linkedTaskId] = false;
                });
        },
        computeAvailableTasks(projectId, taskId) {
            const allTasks = tasksStore.getTasks(projectId);
            const asArray = Object.entries(allTasks)
                .filter(([key]) => key !== taskId)
                .filter(([key]) => !this.links[taskId] || !this.links[taskId][key]);

            if (!this.availableTasks[taskId]) {
                this.availableTasks[taskId] = {};
            }
            for (const [key, value] of asArray) {
                this.availableTasks[taskId][value.id] = value;
            }
        }
    }
});