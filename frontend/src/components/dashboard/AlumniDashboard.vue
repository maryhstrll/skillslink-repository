<template>
  <div class="space-y-6">
    <!-- Page Header -->
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
    >
      <div>
        <h1 class="text-3xl font-bold text-base-content">Alumni Dashboard</h1>
        <p class="text-base-content/70 mt-1">
          Welcome back, {{ alumniName }}! Stay connected with your alma mater
          and manage your profile.
        </p>
      </div>
      <div class="flex gap-2">
        <button class="btn-primary-custom btn" @click="refreshData">
          <i
            class="fas fa-sync-alt w-4 h-4"
            :class="{ 'animate-spin': loading }"
          ></i>
          Refresh
        </button>
        <button class="btn-secondary-custom btn" @click="navigateToProfile">
          <i class="fas fa-user w-4 h-4"></i>
          View Profile
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-20">
      <div class="loading loading-spinner loading-lg"></div>
      <span class="ml-4 text-lg">Loading dashboard data...</span>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          v-for="(card, index) in statCardsData"
          :key="index"
          :title="card.title"
          :value="card.value"
          :description="card.description"
          :icon="card.icon"
          :variant="card.variant"
          :format="card.format"
          :clickable="card.clickable"
          @click="card.action"
        />
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Notifications Card -->
        <div
          class="card bg-base-100 shadow-xl app-surface-secondary"
          v-if="notifications.length > 0"
        >
          <div class="card-body">
            <h2 class="card-title">
              <i class="fas fa-bell text-warning"></i>
              Recent Notifications
              <div class="badge badge-warning" v-if="unreadCount > 0">
                {{ unreadCount }}
              </div>
            </h2>
            <div class="space-y-3 max-h-64 overflow-y-auto">
              <div
                v-for="notification in notifications.slice(0, 3)"
                :key="notification.notification_id"
                class="p-3 rounded-lg border-l-4 cursor-pointer hover:shadow-md transition-shadow"
                :class="[
                  getPriorityClass(notification.priority),
                  notification.status === 'read' ? 'opacity-60' : '',
                ]"
                @click="handleNotificationClick(notification)"
              >
                <div class="flex items-start justify-between mb-1">
                  <div class="flex items-center">
                    <i
                      :class="getCategoryIcon(notification.category)"
                      class="mr-2 text-sm"
                    ></i>
                    <h4 class="font-semibold text-sm">
                      {{ notification.title }}
                    </h4>
                  </div>
                  <div class="flex items-center">
                    <span
                      v-if="notification.status !== 'read'"
                      class="w-2 h-2 bg-blue-500 rounded-full mr-2"
                    ></span>
                    <span class="text-xs text-gray-500">
                      {{ formatNotificationDate(notification.created_at) }}
                    </span>
                  </div>
                </div>
                <p class="text-sm text-gray-700 leading-tight">
                  {{ notification.message }}
                </p>
              </div>
            </div>
            <div
              class="card-actions justify-end"
              v-if="notifications.length > 3"
            >
              <router-link
                to="/alumni/notifications"
                class="btn-warning-custom btn btn-sm"
                >View All</router-link
              >
            </div>
          </div>
        </div>

        <!-- Document Requests Card -->
        <div class="card bg-base-100 shadow-xl app-surface-secondary">
          <div class="card-body">
            <h2 class="card-title">
              <i class="fas fa-file-download text-info"></i>
              Document Requests
            </h2>
            <p class="text-base-content/70 mb-4">
              Request official documents from your alma mater such as
              transcripts, diplomas, and certificates.
            </p>
            <div class="card-actions justify-end">
              <button
                class="btn-info-custom btn"
                @click="router.push('/alumni_document_requests')"
              >
                <i class="fas fa-plus mr-2"></i>
                Request Documents
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Information Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Announcements Card -->
        <div class="card bg-base-100 shadow-xl app-surface-secondary">
          <div class="card-body">
            <h2 class="card-title">
              <i class="fas fa-bullhorn text-secondary"></i>
              Announcements
            </h2>
            <div v-if="announcements.length" class="space-y-2">
              <div
                v-for="(item, index) in announcements"
                :key="index"
                class="p-2 bg-base-200 rounded"
              >
                {{ item }}
              </div>
            </div>
            <div v-else class="text-base-content/70">
              <p>No new announcements at this time.</p>
            </div>
          </div>
        </div>

        <!-- Upcoming Events Card -->
        <div class="card bg-base-100 shadow-xl app-surface-secondary">
          <div class="card-body">
            <h2 class="card-title">
              <i class="fas fa-calendar-alt text-ghost"></i>
              Upcoming Events
            </h2>
            <div v-if="events.length" class="space-y-2">
              <div
                v-for="(event, index) in events"
                :key="index"
                class="p-2 bg-base-200 rounded"
              >
                {{ event }}
              </div>
            </div>
            <div v-else class="text-base-content/70">
              <p>No upcoming events scheduled.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import StatCard from "@/components/cards/StatCard.vue";
import alumniService from "@/services/alumni.js";
import notificationService from "@/services/notificationService.js";
import {
  FileText as IconFileText,
  User as IconUser,
  Bell as IconBell,
  Download as IconDownload,
  UserCheck as IconUserCheck,
} from "lucide-vue-next";

const router = useRouter();

// State
const alumniName = ref(localStorage.getItem("username") || "Alumni");
const tracerStatus = ref("Loading..."); // or "Submitted"
const tracerDesc = ref("Checking form status...");
const tracerSubmitted = ref(false);
const profileCompletion = ref(0);
const loading = ref(true);

// Notification state
const notifications = ref([]);
const unreadCount = ref(0);

// Static data - you can later connect these to actual APIs
const announcements = ref([]);
const events = ref([]);

// Computed properties for stat cards
const statCardsData = computed(() => [
  {
    title: "Tracer Form",
    value: tracerStatus.value,
    description: tracerDesc.value,
    icon: IconFileText,
    variant: tracerSubmitted.value
      ? "accent"
      : tracerStatus.value === "Error"
      ? "danger"
      : "primary",
    clickable: true,
    action: () => navigateToTracerForm(),
  },
  {
    title: "Profile Completion",
    value: profileCompletion.value,
    description:
      profileCompletion.value < 100
        ? "Complete your profile"
        : "Profile complete!",
    icon: IconUserCheck,
    variant: "ghost",
    format: "percentage",
    clickable: true,
    action: () => navigateToProfile(),
  },
  {
    title: "Notifications",
    value: notifications.value.length,
    description:
      unreadCount.value > 0 ? `${unreadCount.value} unread` : "All caught up",
    icon: IconBell,
    variant: unreadCount.value > 0 ? "warning" : "neutral",
    clickable: true,
    action: () => router.push("/alumni/notifications"),
  },
  {
    title: "Documents",
    value: "Available",
    description: "Request official documents",
    icon: IconDownload,
    variant: "secondary",
    clickable: true,
    action: () => router.push("/alumni_document_requests"),
  },
]);

// Utility methods for tracer status
const getTracerStatusBadgeClass = () => {
  if (tracerSubmitted.value) return "badge-success";
  if (tracerStatus.value === "Error") return "badge-error";
  return "badge-warning";
};

// Fetch notifications
const fetchNotifications = async () => {
  try {
    const result = await notificationService.getNotifications();
    if (result.success) {
      notifications.value = result.data || [];
      const countResult = await notificationService.getUnreadCount();
      if (countResult.success) {
        unreadCount.value = countResult.count;
      }
    } else {
      console.error("Error fetching notifications:", result.error);
    }
  } catch (error) {
    console.error("Error fetching notifications:", error);
  }
};

// Handle notification click
const handleNotificationClick = async (notification) => {
  // Mark as read if not already read
  if (notification.status !== "read") {
    const result = await notificationService.markAsRead(
      notification.notification_id
    );
    if (result.success) {
      notification.status = "read";
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  }

  // Handle specific notification actions
  if (
    notification.category === "announcement" &&
    notification.title.includes("Tracer Form")
  ) {
    // Refresh tracer status when tracer form notification is clicked
    await fetchTracerStatus();
  }
};

// Utility methods for notifications
const getPriorityClass = (priority) => {
  return notificationService.getPriorityClass(priority);
};

const getCategoryIcon = (category) => {
  return notificationService.getCategoryIcon(category);
};

const formatNotificationDate = (dateString) => {
  return notificationService.formatNotificationDate(dateString);
};

// Fetch profile data to get real completion percentage
const fetchProfileData = async () => {
  try {
    loading.value = true;
    const profileData = await alumniService.getProfile();

    if (profileData && !profileData.error) {
      // Calculate completion based on the new enhanced criteria
      const fields = [
        // Personal Information
        profileData.personal_info?.phone_number,
        profileData.personal_info?.alternative_phone,
        profileData.personal_info?.date_of_birth,
        profileData.personal_info?.gender,
        // Address Information
        profileData.address?.barangay,
        profileData.address?.city,
        profileData.address?.province,
        profileData.address?.country,
        profileData.address?.postal_code,
        // Academic Information
        profileData.academic_info?.graduation_date,
        profileData.academic_info?.gpa,
        // Social Links
        profileData.social_links?.linkedin_profile,
        profileData.social_links?.facebook_profile
      ];
      
      const completedFields = fields.filter(field => field && field.toString().trim() !== '').length;
      
      // Use server-side calculation or fallback to client-side
      profileCompletion.value = profileData.profile_completion || 
                               Math.round((completedFields / fields.length) * 100);
      
      // Update alumni name from profile if available
      if (profileData.user_info?.full_name) {
        alumniName.value = profileData.user_info.full_name;
      }
    } else {
      console.error("Error fetching profile data:", profileData?.error);
      // Keep default values if profile fetch fails
    }
  } catch (error) {
    console.error("Error fetching profile data:", error);
    // Keep default values if profile fetch fails
  } finally {
    loading.value = false;
  }
};

// Fetch tracer form status
const fetchTracerStatus = async () => {
  try {
    const statusData = await alumniService.getTracerStatus();

    if (statusData.success) {
      tracerStatus.value = statusData.status || "Unknown";
      tracerDesc.value = statusData.description || "No description available";
      tracerSubmitted.value = statusData.submitted || false;
    } else {
      console.error("Error fetching tracer status:", statusData.error);
      tracerStatus.value = "Error";
      tracerDesc.value =
        statusData.description || "Unable to fetch form status";
      tracerSubmitted.value = false;
    }
  } catch (error) {
    console.error("Error fetching tracer status:", error);
    tracerStatus.value = "Error";
    tracerDesc.value = "Unable to connect to server";
    tracerSubmitted.value = false;
  }
};

// Navigate to profile page
const navigateToProfile = () => {
  router.push("/alumni_profile");
};

// Navigate to TRACER Form
const navigateToTracerForm = () => {
  router.push("/alumni_tracer_form");
};

// Refresh all dashboard data
const refreshData = async () => {
  await Promise.all([
    fetchProfileData(),
    fetchTracerStatus(),
    fetchNotifications(),
  ]);
};

// Fetch data when component mounts
onMounted(() => {
  refreshData();
});
</script>

<style scoped>
h1, p {
  color: var(--color-text-primary);
}

</style>