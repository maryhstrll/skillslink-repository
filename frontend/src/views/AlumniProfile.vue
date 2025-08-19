<template>
  <Layout @logout="handleLogout">
    <div class="p-6 max-w-5xl mx-auto">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="loading loading-spinner loading-lg"></div>
        <span class="ml-2">Loading profile...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="alert alert-error shadow-lg mb-6">
        <div>
          <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="font-bold">Unable to Load Profile</h3>
            <div class="text-xs">{{ error }}</div>
            <div class="mt-2">
              <button @click="$router.push('/home')" class="btn btn-sm btn-primary">
                Go to Login
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Content -->
      <div v-else-if="profile">
        <!-- Profile Card -->
        <div class="card bg-base-200 shadow-xl rounded-2xl mb-6">
          <div class="card-body flex flex-col sm:flex-row items-center sm:items-start gap-6">
            <!-- Profile Image -->
            <div class="avatar">
              <div class="w-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                <img 
                  v-if="profile?.user_info?.profile_image && !imageError"
                  :src="profile.user_info.profile_image" 
                  :alt="profile.user_info.full_name + ' Profile'"
                  @error="handleImageError"
                  class="w-full h-full object-cover"
                />
                <div 
                  v-else
                  class="w-full h-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-3xl font-bold"
                >
                  {{ getInitials(profile?.user_info?.full_name) }}
                </div>
              </div>
            </div>

            <!-- Profile Info -->
            <div class="flex-1">
              <div class="flex-1">
                <!-- Name + Batch -->
                <div class="flex items-center gap-2">
                  <h2 class="card-title text-2xl">{{ profile.user_info.full_name || 'No Name Available' }}</h2>
                  <span v-if="profile.academic_info.batch_name" class="badge badge-primary">
                    {{ profile.academic_info.batch_name }}
                  </span>
                </div>

                <!-- Student ID -->
                <p class="text-sm text-gray-500 mt-1">
                  Student ID: {{ profile.user_info.student_id || 'N/A' }}
                </p>

                <!-- Program + Graduation -->
                <p class="text-sm text-gray-500">
                  {{ profile.academic_info.program_name || 'Program not specified' }}
                </p>
                <p class="text-sm text-gray-500">
                  Graduated: {{ formatDate(profile.academic_info.graduation_date) || 'Not specified' }}
                </p>

                <!-- Progress Bar -->
                <div class="mt-3">
                  <label class="text-sm font-medium">Profile Completion</label>
                  <progress
                    class="progress progress-success w-56"
                    :value="profile.profile_completion"
                    max="100"
                  ></progress>
                  <span class="text-xs text-gray-500 ml-2">{{ profile.profile_completion }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabs -->
        <div class="tabs tabs-boxed mb-4">
          <a :class="['tab', activeTab === 'overview' ? 'tab-active' : '']" @click="activeTab = 'overview'">Overview</a>
          <a :class="['tab', activeTab === 'edit' ? 'tab-active' : '']" @click="activeTab = 'edit'">Edit Profile</a>
          <a :class="['tab', activeTab === 'social' ? 'tab-active' : '']" @click="activeTab = 'social'">Social Links</a>
        </div>

        <!-- Tab Content -->
        <div v-if="activeTab === 'overview'">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow-md rounded-xl">
              <div class="card-body">
                <h3 class="card-title text-lg">Personal Information</h3>
                <p><strong>Full Name:</strong> {{ profile.user_info.full_name || 'Not provided' }}</p>
                <p><strong>Gender:</strong> {{ profile.personal_info.gender || 'Not specified' }}</p>
                <p><strong>Date of Birth:</strong> {{ formatDate(profile.personal_info.date_of_birth) || 'Not provided' }}</p>
                <p><strong>Phone:</strong> {{ profile.personal_info.phone_number || 'Not provided' }}</p>
                <p><strong>Alternative Phone:</strong> {{ profile.personal_info.alternative_phone || 'Not provided' }}</p>
              </div>
            </div>

            <div class="card bg-base-100 shadow-md rounded-xl">
              <div class="card-body">
                <h3 class="card-title text-lg">Address</h3>
                <p><strong>Barangay:</strong> {{ profile.address.barangay || 'Not provided' }}</p>
                <p><strong>City:</strong> {{ profile.address.city || 'Not provided' }}</p>
                <p><strong>Province:</strong> {{ profile.address.province || 'Not provided' }}</p>
                <p><strong>Country:</strong> {{ profile.address.country || 'Philippines' }}</p>
                <p><strong>Postal Code:</strong> {{ profile.address.postal_code || 'Not provided' }}</p>
              </div>
            </div>
            
            <div class="card bg-base-100 shadow-md rounded-xl">
              <div class="card-body">
                <h3 class="card-title text-lg">Academic Information</h3>
                <p><strong>Program:</strong> {{ profile.academic_info.program_name || 'Not specified' }}</p>
                <p><strong>Department:</strong> {{ profile.academic_info.department || 'Not specified' }}</p>
                <p><strong>Batch:</strong> {{ profile.academic_info.batch_name || 'Not specified' }}</p>
                <p><strong>Year Graduated:</strong> {{ profile.academic_info.year_graduated || 'Not specified' }}</p>
                <p><strong>GPA:</strong> {{ profile.academic_info.gpa || 'Not provided' }}</p>
              </div>
            </div>

            <div class="card bg-base-100 shadow-md rounded-xl">
              <div class="card-body">
                <h3 class="card-title text-lg">Employment Status</h3>
                <p><strong>Status:</strong> {{ formatEmploymentStatus(profile.employment.status) || 'Not specified' }}</p>
                <p v-if="profile.employment.company"><strong>Company:</strong> {{ profile.employment.company }}</p>
                <p v-if="profile.employment.position"><strong>Position:</strong> {{ profile.employment.position }}</p>
                <p v-if="profile.employment.employment_type"><strong>Employment Type:</strong> {{ formatEmploymentType(profile.employment.employment_type) }}</p>
                <p v-if="profile.employment.work_setup"><strong>Work Setup:</strong> {{ formatWorkSetup(profile.employment.work_setup) }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else-if="activeTab === 'edit'">
          <AlumniProfileEdit :profile="profile" @updated="fetchProfile" />
        </div>
        <div v-else-if="activeTab === 'social'">
          <SocialLinksEdit :profile="profile" @updated="fetchProfile" />
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import Layout from "@/components/Layout.vue";
import AlumniProfileEdit from "@/components/AlumniProfileEdit.vue";
import SocialLinksEdit from "@/components/SocialLinksEdit.vue";
import { useRouter } from "vue-router";
import { ref, onMounted } from "vue";
import alumniService from "@/services/alumni.js";

const router = useRouter();
const profile = ref(null);
const loading = ref(true);
const error = ref(null);
const imageError = ref(false);
const activeTab = ref('overview');

const handleLogout = () => {
  router.push("/home");
};

const fetchProfile = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    const profileData = await alumniService.getProfile();
    
    if (profileData && !profileData.error) {
      profile.value = profileData;
    } else if (profileData && profileData.error) {
      // Handle specific API errors
      if (profileData.error.includes('Unauthorized')) {
        error.value = 'You must be logged in as an alumni user to view your profile.';
      } else if (profileData.error.includes('Access denied')) {
        error.value = 'Access denied. This page is only available to alumni users.';
      } else {
        error.value = profileData.error;
      }
    } else {
      error.value = 'Failed to load profile data. Please make sure you are logged in as an alumni user.';
    }
  } catch (err) {
    console.error('Error fetching profile:', err);
    error.value = 'An error occurred while loading your profile';
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return null;
  
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (error) {
    return dateString;
  }
};

const formatEmploymentStatus = (status) => {
  if (!status) return null;
  
  const statusMap = {
    'employed': 'Employed',
    'unemployed': 'Unemployed',
    'self_employed': 'Self Employed',
    'further_studies': 'Further Studies',
    'not_looking': 'Not Looking for Work'
  };
  
  return statusMap[status] || status;
};

const formatEmploymentType = (type) => {
  if (!type) return null;
  
  const typeMap = {
    'full_time': 'Full Time',
    'part_time': 'Part Time',
    'contract': 'Contract',
    'freelance': 'Freelance',
    'internship': 'Internship'
  };
  
  return typeMap[type] || type;
};

const formatWorkSetup = (setup) => {
  if (!setup) return null;
  
  const setupMap = {
    'onsite': 'On-site',
    'remote': 'Remote',
    'hybrid': 'Hybrid'
  };
  
  return setupMap[setup] || setup;
};

const getInitials = (fullName) => {
  if (!fullName) return '?';
  
  const names = fullName.trim().split(' ');
  if (names.length === 1) {
    return names[0].charAt(0).toUpperCase();
  }
  
  // Get first letter of first name and last name
  const firstInitial = names[0].charAt(0).toUpperCase();
  const lastInitial = names[names.length - 1].charAt(0).toUpperCase();
  
  return firstInitial + lastInitial;
};

const handleImageError = () => {
  imageError.value = true;
};

onMounted(() => {
  fetchProfile();
});
</script>
