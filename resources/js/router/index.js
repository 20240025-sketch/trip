import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/Home.vue'),
  },
  {
    path: '/plans',
    name: 'plans',
    component: () => import('@/views/PlanIndex.vue'),
  },
  {
    path: '/plans/create',
    name: 'plans.create',
    component: () => import('@/views/PlanCreate.vue'),
  },
  {
    path: '/plans/:id',
    name: 'plans.show',
    component: () => import('@/views/PlanShow.vue'),
  },
  {
    path: '/plans/:id/edit',
    name: 'plans.edit',
    component: () => import('@/views/PlanEdit.vue'),
  },
  {
    path: '/p/:slug',
    name: 'plans.public',
    component: () => import('@/views/PlanPublic.vue'),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
