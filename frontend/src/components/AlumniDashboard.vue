<template>
  <div class="space-y-6">
    <!-- Welcome Section -->
    <div class="hero bg-primary text-primary-content rounded-lg">
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
      <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-figure" :class="tracerSubmitted ? 'text-success' : tracerStatus === 'Error' ? 'text-error' : 'text-primary'">
          <i class="text-3xl fas" :class="tracerSubmitted ? 'fa-check-circle' : tracerStatus === 'Error' ? 'fa-exclamation-triangle' : 'fa-file-alt'"></i>
        </div>
        <div class="stat-title">Tracer Form</div>
        <div class="stat-value" :class="tracerSubmitted ? 'text-success' : tracerStatus === 'Error' ? 'text-error' : 'text-primary'">
          {{ tracerStatus }}
        </div>
        <div class="stat-desc">{{ tracerDesc }}</div>
      </div>

      <!-- Profile Completion -->
      <div class="stat bg-base-100 rounded-lg shadow cursor-pointer hover:shadow-lg transition-shadow duration-300" 
           @click="navigateToProfile">
        <div class="stat-figure text-secondary">
          <i class="fas fa-user-check text-3xl"></i>
        </div>
        <div class="stat-title">Profile Completion</div>
        <div class="stat-value text-secondary">
          {{ loading ? '...' : profileCompletion }}%
        </div>
        <div class="stat-desc">
          {{ profileCompletion < 100 ? 'Complete your profile for better opportunities' : 'Your profile is complete!' }}
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

    <!-- Upcoming Events -->
    <div class="card bg-base-100 shadow-xl">
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
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import alumniService from "@/services/alumni.js";

const router = useRouter();

// State
const alumniName = ref(localStorage.getItem("username") || "Alumni");
const tracerStatus = ref("Loading..."); // or "Submitted"
const tracerDesc = ref("Checking form status...");
const tracerSubmitted = ref(false);
const profileCompletion = ref(0);
const loading = ref(true);

// Static data - you can later connect these to actual APIs
const announcements = ref([]);

const events = ref([]);

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

// Fetch data when component mounts
onMounted(() => {
  fetchProfileData();
  fetchTracerStatus();
});
</script>
