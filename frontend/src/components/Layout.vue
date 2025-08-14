<template>
  <div class="min-h-screen bg-base-200">
    <!-- Navbar -->
    <nav class="navbar bg-base-100 shadow-lg">
      <div class="flex-none lg:hidden">
        <label for="my-drawer" class="btn btn-square btn-ghost">
          <IconMenu class="text-lg" />
        </label>
      </div>
      <div class="flex-1">
        <button class="btn btn-circle">
          <img
            src="../assets/logo1.png"
            alt="Logo 1"
            class="w-[3em] h-[3em] object-contain"
          />
        </button>
        <router-link
          to="/dashboard"
          class="btn btn-ghost text-xl font-bold text-primary"
        >
          SSVTC - SkillsLink
        </router-link>
      </div>
      <div class="flex-none">
        <!-- Notifications -->
        <div class="dropdown dropdown-end">
          <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
            <div class="indicator">
              <IconBell class="text-lg" />
              <span class="badge badge-xs badge-primary indicator-item">3</span>
            </div>
          </div>
          <div
            tabindex="0"
            class="dropdown-content card card-compact w-64 bg-base-100 shadow"
          >
            <div class="card-body">
              <span class="font-bold text-lg">3 Notifications</span>
              <span class="text-info">You have new messages</span>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Drawer -->
    <div class="drawer lg:drawer-open">
      <input id="my-drawer" type="checkbox" class="drawer-toggle" />

      <!-- Main Content -->
      <div class="drawer-content">
        <!-- Page content -->
        <main class="p-4 lg:p-6">
          <slot />
        </main>
      </div>

      <!-- Sidebar -->
      <Sidebar
        :menu-items="menuItems"
        :user-name="displayUserName"
        @profile="handleProfile"
        @logout="handleLogout"
      />
    </div>
  </div>
</template>

<script setup>
import Sidebar from "./Sidebar.vue";
import authService from "@/services/auth.js";
import { messageService } from "@/services/messageService.js";
import { useRouter } from "vue-router";
import { ref, onMounted, computed } from "vue";

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
    path: "/dashboard",
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
    path: "/tracer_forms_admin",
    label: "Tracer Form",
    icon: "IconFileText",
    roles: ["admin"] // Admin and staff only
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
    path: "/users",
    label: "Users",
    icon: "IconUserCog",
    roles: ["admin"] // Admin only
  },
  {
    path: "/settings",
    label: "Settings",
    icon: "IconSettings",
    roles: ["admin", "alumni", "staff"] // Available to all roles
  },
];

// Filter menu items based on user role
const menuItems = computed(() => {
  if (!currentUser.value || !currentUser.value.role) {
    // If no user or role, show only dashboard (safe default)
    return allMenuItems.filter(item => item.path === "/dashboard");
  }
  
  const userRole = currentUser.value.role;
  return allMenuItems.filter(item => item.roles.includes(userRole));
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
