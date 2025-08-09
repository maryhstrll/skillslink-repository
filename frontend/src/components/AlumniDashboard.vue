<template>
  <div class="space-y-6">
    <!-- Welcome Section -->
    <div class="hero bg-primary text-primary-content rounded-lg">
      <div class="hero-content text-center">
        <div class="max-w-md">
          <h1 class="mb-5 text-3xl font-bold">
            Welcome Back, {{ alumniName }}
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
        <div class="stat-figure text-primary">
          <i class="fas fa-file-alt text-3xl"></i>
        </div>
        <div class="stat-title">Tracer Form</div>
        <div class="stat-value text-primary">{{ tracerStatus }}</div>
        <div class="stat-desc">{{ tracerDesc }}</div>
      </div>

      <!-- Profile Completion -->
      <div class="stat bg-base-100 rounded-lg shadow">
        <div class="stat-figure text-secondary">
          <i class="fas fa-user-check text-3xl"></i>
        </div>
        <div class="stat-title">Profile Completion</div>
        <div class="stat-value text-secondary">{{ profileCompletion }}%</div>
        <div class="stat-desc">
          Complete your profile for better opportunities
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
import { ref } from "vue";

// Example: Replace with actual fetched data from API
const alumniName = ref(localStorage.getItem("username") || "Alumni");
const tracerStatus = ref("Not Submitted"); // or "Submitted"
const tracerDesc = ref("Submit your form for the current year.");
const profileCompletion = ref(75); // %

const announcements = ref([
  "Job Fair 2025 - March 15",
  "PUP Alumni Homecoming - April 10",
]);

const events = ref([
  "Webinar: Career Growth Strategies - Feb 25",
  "On-campus Recruitment - March 5",
]);
</script>
