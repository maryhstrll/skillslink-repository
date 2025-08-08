import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Dashboard from '@/views/Dashboard.vue';
import Alumni from '@/views/Alumni.vue';
import Reports from '@/views/Reports.vue';
import Settings from '@/views/Settings.vue';
import Users from '@/views/Users.vue';

const routes = [
  { path: '/home', component: Home },
  { path: '/dashboard', component: Dashboard },
  { path: '/alumni', component: Alumni },
  { path: '/reports', component: Reports },
  { path: '/settings', component: Settings },
  { path: '/users', component: Users },
  // Redirect any unknown routes to home
  { path: '/', redirect: '/home' },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Route protection
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem('user') !== null;
  
  // Public routes that don't require authentication
  const publicRoutes = ['/', '/home'];
  
  if (!publicRoutes.includes(to.path) && !isAuthenticated) {
    // Redirect to home if not authenticated
    next('/home');
  } else if (to.path === '/' && isAuthenticated) {
    // If user is authenticated and goes to root, redirect to dashboard
    next('/dashboard');
  } else {
    next();
  }
});

export default router;