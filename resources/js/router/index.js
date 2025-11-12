import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/Home.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/Login.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/Register.vue'),
    meta: { guest: true },
  },
  {
    path: '/change-password',
    name: 'changePassword',
    component: () => import('@/views/ChangePassword.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/qa',
    name: 'qa',
    component: () => import('@/pages/QuestionsPage.vue'),
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
    meta: { requiresAuth: true },
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
    meta: { requiresAuth: true },
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

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } });
  }
  // Check if route is for guests only (login/register)
  else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'plans' });
  }
  else {
    next();
  }
});

export default router;
