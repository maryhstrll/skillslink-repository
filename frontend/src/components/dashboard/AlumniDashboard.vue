<template>
  <div class="space-y-6">
    <!-- Welcome Section -->
    <div class="hero rounded-lg" style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white;">
      <div class="hero-content text-center">
        <div class="max-w-md">
          <h1 class="mb-5 text-3xl font-bold">
            Welcome Back, <br>{{ alumniName }}
          </h1>
          <p class="mb-5">
            Stay connected with your alma mater and keep your profile updated.
          </p>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
      <!-- Tracer Form Status -->
      <div class="stat-card cursor-pointer hover:shadow-lg transition-all duration-300 hover:-translate-y-1" 
           :class="tracerSubmitted ? 'stat-card-accent' : tracerStatus === 'Error' ? 'stat-card-danger' : 'stat-card-primary'"
           @click="navigateToTracerForm">
        <div class="stat-figure" :class="tracerSubmitted || tracerStatus === 'Error' ? 'text-white' : 'text-white'">
          <i class="text-3xl fas" :class="tracerSubmitted ? 'fa-check-circle' : tracerStatus === 'Error' ? 'fa-exclamation-triangle' : 'fa-file-alt'"></i>
        </div>
        <div class="stat-title" :class="tracerSubmitted || tracerStatus === 'Error' ? 'text-white/80' : 'text-white/80'">Tracer Form</div>
        <div class="stat-value" :class="tracerSubmitted || tracerStatus === 'Error' ? 'text-white' : 'text-white'">
          {{ tracerStatus }}
        </div>
        <div class="stat-desc" :class="tracerSubmitted || tracerStatus === 'Error' ? 'text-white/70' : 'text-white/70'">{{ tracerDesc }}</div>
      </div>

      <!-- Profile Completion -->
      <div class="stat-card stat-card-ghost cursor-pointer hover:shadow-lg transition-all duration-300 hover:-translate-y-1" 
           @click="navigateToProfile">
        <div class="stat-figure">
          <i class="fas fa-user-check text-3xl"></i>
        </div>
        <div class="stat-title">Profile Completion</div>
        <div class="stat-value">
          {{ loading ? '...' : profileCompletion }}%
        </div>
        <div class="stat-desc">
          {{ profileCompletion < 100 ? 'Complete your profile for better opportunities' : 'Your profile is complete!' }}
        </div>
      </div>
    </div>

    <!-- Notifications Section -->
    <div class="card bg-base-100 shadow-xl" v-if="notifications.length > 0">
      <div class="card-body">
        <h2 class="card-title">
          <i class="fas fa-bell"></i>
          Recent Notifications
          <div class="badge badge-primary" v-if="unreadCount > 0">{{ unreadCount }}</div>
        </h2>
        <div class="space-y-3">
          <div 
            v-for="notification in notifications.slice(0, 3)" 
            :key="notification.notification_id"
            class="p-3 rounded-lg border-l-4 cursor-pointer hover:shadow-md transition-shadow"
            :class="[
              getPriorityClass(notification.priority),
              notification.status === 'read' ? 'opacity-60' : ''
            ]"
            @click="handleNotificationClick(notification)"
          >
            <div class="flex items-start justify-between mb-1">
              <div class="flex items-center">
                <i :class="getCategoryIcon(notification.category)" class="mr-2 text-sm"></i>
                <h4 class="font-semibold text-sm">{{ notification.title }}</h4>
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
            <p class="text-sm text-gray-700 leading-tight">{{ notification.message }}</p>
          </div>
        </div>
        <div class="card-actions justify-end" v-if="notifications.length > 3">
          <router-link to="/alumni/notifications" class="btn-primary-custom btn btn-sm">View All</router-link>
        </div>
      </div>
    </div>

    <!-- Announcements -->
    <div class="card bg-base-100 shadow-xl">
      <div class="card-body">
        <h2 class="card-title">
          <i class="fas fa-bullhorn"></i>
          Announcements
        </h2>
        <div v-if="announcements.length">
          <ul class="list-disc list-inside">
            <li v-for="(item, index) in announcements" :key="index">
              {{ item }}
            </li>
          </ul>
        </div>
        <p v-else>No new announcements.</p>
      </div>
    </div>

    <!-- Tracer Form Status -->
    <div class="card bg-base-100 shadow-xl" v-if="events.length">
      <div class="card-body">
        <h2 class="card-title">
          <i class="fas fa-calendar-alt"></i>
          Upcoming Events
        </h2>
        <div v-if="events.length">
          <ul class="list-disc list-inside">
            <li v-for="(event, index) in events" :key="index">
              {{ event }}
            </li>
          </ul>
        </div>
        <p v-else>No upcoming events.</p>
      </div>
    </div>

    <!-- Document Requests -->
    <div class="card bg-base-100 shadow-xl">
      <div class="card-body">
        <h2 class="card-title">
          <i class="fas fa-file-alt"></i>
          Document Requests
        </h2>
        <p class="text-base-content/70 mb-4">
          Request official documents from your alma mater such as transcripts, diplomas, and certificates.
        </p>
        <div class="card-actions justify-end">
          <button 
            class="btn-accent-custom btn"
            @click="router.push('/alumni_document_requests')"
          >
            <i class="fas fa-plus mr-2"></i>
            Request Documents
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import alumniService from "@/services/alumni.js";
import notificationService from "@/services/notificationService.js";

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
      console.error('Error fetching notifications:', result.error);
    }
  } catch (error) {
    console.error('Error fetching notifications:', error);
  }
};

// Handle notification click
const handleNotificationClick = async (notification) => {
  // Mark as read if not already read
  if (notification.status !== 'read') {
    const result = await notificationService.markAsRead(notification.notification_id);
    if (result.success) {
      notification.status = 'read';
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  }
  
  // Handle specific notification actions
  if (notification.category === 'announcement' && notification.title.includes('Tracer Form')) {
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
      profileCompletion.value = profileData.profile_completion || 0;
      // Update alumni name from profile if available
      if (profileData.user_info?.full_name) {
        alumniName.value = profileData.user_info.full_name;
      }
    } else {
      console.error('Error fetching profile data:', profileData?.error);
      // Keep default values if profile fetch fails
    }
  } catch (error) {
    console.error('Error fetching profile data:', error);
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
      console.error('Error fetching tracer status:', statusData.error);
      tracerStatus.value = "Error";
      tracerDesc.value = statusData.description || "Unable to fetch form status";
      tracerSubmitted.value = false;
    }
  } catch (error) {
    console.error('Error fetching tracer status:', error);
    tracerStatus.value = "Error";
    tracerDesc.value = "Unable to connect to server";
    tracerSubmitted.value = false;
  }
};

// Navigate to profile page
const navigateToProfile = () => {
  router.push('/alumni_profile');
};

// Navigate to TRACER Form
const navigateToTracerForm = () => {
  router.push('/alumni_tracer_form');
};

// Fetch data when component mounts
onMounted(() => {
  fetchProfileData();
  fetchTracerStatus();
  fetchNotifications();
});
</script>
