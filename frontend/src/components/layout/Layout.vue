<template>
  <div class="min-h-screen" style="background: var(--color-bg);">
    <!-- Drawer -->
    <div class="drawer lg:drawer-open">
      <input id="my-drawer" type="checkbox" class="drawer-toggle" />

      <!-- Main Content -->
      <div class="drawer-content flex flex-col">
        <!-- Navbar - now inside main content area -->
        <nav class="navbar shadow-m sticky top-0 z-50 p-3 navbar-custom">
          <div class="flex-none lg:hidden">
            <label for="my-drawer" class="btn btn-square btn-ghost border-0 text-white hover:bg-white/10">
              <IconMenu class="text-lg" />
            </label>
          </div>
          <div class="flex-1 flex items-center">
              <img
                src="../../assets/logo1.png"
                alt="Logo 1"
                class="w-[3.2em] h-[3.2em] object-contain mr-2"
              />
            <router-link
              to="/dashboard"
              class="btn btn-ghost border-0 text-[25px] font-bold p-2 text-white hover:bg-white/10"
              style="color: white;"
            >
              SSVTC - SkillsLink
            </router-link>
          </div>
          <div class="bell flex-none mr-2">
            <!-- Notifications -->
            <NotificationBell v-if="currentUser && currentUser.role === 'alumni'" />
          </div>
        </nav>
        
        <!-- Page content -->
        <main class="flex-1 p-4 lg:p-6" style="color: var(--color-text-primary);">
          <slot />
        </main>
      </div>

      <!-- Sidebar -->
      <Sidebar
        :menu-items="menuItems"
        :user-name="displayUserName"
        :user-role="currentUser?.role || 'alumni'"
        @profile="handleProfile"
        @logout="handleLogout"
      />
    </div>
  </div>
</template>

<script setup>
import Sidebar from "./Sidebar.vue";
import NotificationBell from "../ui/NotificationBell.vue";
import authService from "@/services/auth.js";
import { messageService } from "@/services/messageService.js";
import { useRouter } from "vue-router";
import { ref, onMounted, computed } from "vue";
import { Menu as IconMenu } from 'lucide-vue-next';

const props = defineProps({
  userName: {
    type: String,
    default: "User",
  },
});

const emit = defineEmits(["profile", "logout"]);
const router = useRouter();

// User data from authentication service
const currentUser = ref(null);

// Computed property for user name - prioritize real user data over prop
const displayUserName = computed(() => {
  if (currentUser.value) {
    // Use full_name if available, otherwise combine first_name and last_name
    return currentUser.value.full_name || 
           `${currentUser.value.first_name || ''} ${currentUser.value.last_name || ''}`.trim() ||
           currentUser.value.email ||
           'User';
  }
  return props.userName || 'User';
});

// Fetch current user data on mount
onMounted(async () => {
  try {
    const authCheck = await authService.checkAuth();
    if (authCheck.loggedIn && authCheck.user) {
      currentUser.value = authCheck.user;
    } else {
      // If not logged in, redirect to home
      router.push('/home');
    }
  } catch (error) {
    console.error('Failed to check authentication:', error);
    router.push('/home');
  }
});

// Define all possible menu items
const allMenuItems = [
  {
    path: (role) => {
      // Dynamic dashboard path based on user role
      switch (role) {
        case 'admin': return "/admin/dashboard";
        case 'staff': return "/staff/dashboard";
        case 'alumni': return "/alumni/dashboard";
        default: return "/alumni/dashboard";
      }
    },
    label: "Dashboard",
    icon: "IconHome",
    roles: ["admin", "alumni", "staff"] // Available to all roles
  },
  {
    path: "/alumni",
    label: "Alumni",
    icon: "IconUsers",
    roles: ["admin", "staff"] // Admin and staff only
  },
  {
    label: "Tracing",
    icon: "IconRadar",
    hasSubmenu: true,
    roles: ["admin"], // Admin only
    submenu: [
      {
        path: "/tracer_forms_admin",
        label: "Manage Forms",
        roles: ["admin"]
      },
      {
        path: "/form_responses",
        label: "Form Responses",
        roles: ["admin"]
      }
    ]
  },
    {
    path: "/alumni_tracer_form",
    label: "Tracer Form",
    icon: "IconFileInput",
    roles: ["alumni"] // Admin and staff only
  },
  {
    path: "/reports",
    label: "Reports",
    icon: "IconBarChart3",
    roles: ["admin", "staff"] // Admin and staff only
  },
  {
    path: "/manageprograms",
    label: "Manage Programs",
    icon: "IconSquareLibrary",
    roles: ["admin"]
  },
  {
    path: "/alumni_document_requests",
    label: "Document Requests", 
    icon: "IconFileText",
    roles: ["alumni"] // Alumni only
  },
  {
    path: "/admin_document_requests",
    label: "Document Requests",
    icon: "IconFileText", 
    roles: ["admin", "staff"] // Admin and staff only
  },
  {
    path: "/users",
    label: "Users",
    icon: "IconUserCog",
    roles: ["admin"] // Admin only
  },
    {
    path: "/alumni_profile",
    label: "Profile",
    icon: "IconUserRound",
    roles: ["alumni"] // Admin only
  },


];

// Filter menu items based on user role
const menuItems = computed(() => {
  if (!currentUser.value || !currentUser.value.role) {
    // If no user or role, show only dashboard (safe default)
    return allMenuItems.filter(item => typeof item.path === 'function' || item.path === "/dashboard").map(item => ({
      ...item,
      path: typeof item.path === 'function' ? item.path('alumni') : item.path
    }));
  }
  
  const userRole = currentUser.value.role;
  return allMenuItems.filter(item => item.roles.includes(userRole)).map(item => ({
    ...item,
    path: typeof item.path === 'function' ? item.path(userRole) : item.path
  }));
});

const handleProfile = () => {
  emit("profile");
  console.log("Profile clicked");
};

const handleSettings = () => {
  emit("settings");
  router.push("/settings");
};

const handleLogout = async () => {
  const confirmed = await messageService.confirm(
    'Are you sure you want to logout?',
    'Logout Confirmation'
  );
  
  if (confirmed) {
    try {
      const result = await authService.logout();
      if (result.success) {
        messageService.toast('Logged out successfully', 'success');
        emit("logout");
        router.push("/home");
      } else {
        messageService.toast('Logout completed', 'info');
        // Still redirect to home even if API fails
        router.push("/home");
      }
    } catch (error) {
      console.error("Logout error:", error);
      messageService.toast('Logout completed', 'info');
      // Still redirect to home even if there's an error
      router.push("/home");
    }
  }
};
</script>

<style scoped>

</style>
