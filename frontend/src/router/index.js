import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";
import Dashboard from "@/views/Dashboard.vue";
import Alumni from "@/views/Alumni.vue";
import TracerFormsAdmin from "@/views/TracerFormsAdmin.vue";
import AlumniTracerForm from "@/views/AlumniTracerForm.vue";
import Reports from "@/views/Reports.vue";
import AlumniProfile from "@/views/AlumniProfile.vue";
import AdminSettings from "@/views/AdminSettings.vue";
import AlumniSettings from "@/views/AlumniSettings.vue";
import Users from "@/views/Users.vue";

// Helper function to get user role
const getUserRole = () => {
  try {
    const userStr = localStorage.getItem("user");
    if (userStr) {
      const userData = JSON.parse(userStr);
      return userData.role || null;
    }
  } catch (error) {
    console.error("Error parsing user data:", error);
  }
  return null;
};

const routes = [
  { path: "/home", component: Home },
  {
    path: "/dashboard",
    component: Dashboard,
    meta: { requiresAuth: true, roles: ["admin", "alumni", "staff"] },
  },
  {
    path: "/alumni",
    component: Alumni,
    meta: { requiresAuth: true, roles: ["admin", "staff"] },
  },
  {
    path: "/tracer_forms_admin",
    component: TracerFormsAdmin,
    meta: { requiresAuth: true, roles: ["admin"] },
  },
  {
    path: "/alumni_tracer_form",
    component: AlumniTracerForm,
    meta: { requiresAuth: true, roles: ["alumni"] },
  },
  {
    path: "/reports",
    component: Reports,
    meta: { requiresAuth: true, roles: ["admin", "staff"] },
  },
    {
    path: "/alumni_profile",
    component: AlumniProfile,
    meta: { requiresAuth: true, roles: ["alumni"] },
  },
  {
    path: "/admin_settings",
    component: AdminSettings,
    meta: { requiresAuth: true, roles: ["admin"] },
  },
  {
    path: "/alumni_settings", 
    component: AlumniSettings,
    meta: { requiresAuth: true, roles: ["alumni"] },
  },
  {
    path: "/settings",
    redirect: (to) => {
      // Redirect /settings to appropriate settings page based on user role
      const userRole = getUserRole();
      if (userRole === 'admin') {
        return '/admin_settings';
      } else if (userRole === 'alumni') {
        return '/alumni_settings';
      }
      return '/admin_settings'; // default fallback
    }
  },
  {
    path: "/users",
    component: Users,
    meta: { requiresAuth: true, roles: ["admin"] },
  },
  // Redirect any unknown routes to home
  { path: "/", redirect: "/home" },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Route protection
router.beforeEach((to, from, next) => {
  const isAuthenticated = localStorage.getItem("user") !== null;
  const userRole = getUserRole();

  // Public routes that don't require authentication
  const publicRoutes = ["/", "/home"];

  if (!publicRoutes.includes(to.path) && !isAuthenticated) {
    // Redirect to home if not authenticated
    next("/home");
  } else if (to.path === "/" && isAuthenticated) {
    // If user is authenticated and goes to root, redirect to dashboard
    next("/dashboard");
  } else if (to.meta?.requiresAuth && isAuthenticated && to.meta?.roles) {
    // Check role-based access for protected routes
    if (!userRole || !to.meta.roles.includes(userRole)) {
      // User doesn't have permission, redirect to dashboard
      console.warn(
        `Access denied: User role '${userRole}' cannot access '${to.path}'`
      );
      next("/dashboard");
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
