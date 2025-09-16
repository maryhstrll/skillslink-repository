<template>
  <Layout @logout="handleLogout">
    <div class="space-y-6">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center h-64 app-surface-secondary rounded-lg">
        <div class="loading loading-spinner loading-lg text-primary"></div>
        <span class="ml-2 text-primary">Loading profile...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="alert alert-error shadow-lg">
        <div>
          <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="font-bold">Unable to Load Profile</h3>
            <div class="text-xs">{{ error }}</div>
            <div class="mt-2">
              <button @click="$router.push('/home')" class="btn btn-sm btn-primary-custom">
                Go to Login
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Content -->
      <div v-else-if="profile" class="space-y-6">
        <!-- Profile Header -->
        <div class="card bg-base-100 shadow-xl app-surface-secondary">
          <div class="card-body">
            <div class="flex flex-col lg:flex-row items-center lg:items-start gap-6">
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
              <div class="flex-1 text-center lg:text-left">
                <!-- Name + Batch -->
                <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-3">
                  <h1 class="text-3xl font-bold text-primary">{{ profile.user_info.full_name || 'No Name Available' }}</h1>
                  <span v-if="profile.academic_info.batch_name" class="badge badge-primary">
                    {{ profile.academic_info.batch_name }}
                  </span>
                </div>

                <!-- Student ID -->
                <p class="text-lg font-medium mb-1">
                  Student ID: {{ profile.user_info.student_id || 'N/A' }}
                </p>

                <!-- Program + Graduation -->
                <p class="text-base mb-1">
                  {{ profile.academic_info.program_name || 'Program not specified' }}
                </p>
                <p class="text-base mb-4">
                  Graduated: {{ formatDate(profile.academic_info.graduation_date) || 'Not specified' }}
                </p>

                <!-- Progress Bar -->
                <div class="max-w-md mx-auto lg:mx-0">
                  <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-medium">Profile Completion</label>
                    <span class="text-sm font-semibold">{{ profileCompletionPercentage }}%</span>
                  </div>
                  <progress
                    class="progress progress-primary w-full"
                    :value="profileCompletionPercentage"
                    max="100"
                  ></progress>
                </div>
              </div>

              <!-- Edit Profile Button -->
              <div class="mt-4 lg:mt-0">
                <button 
                  @click="navigateToSettings" 
                  class="btn btn-primary-custom px-6"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit Profile
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Profile Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Personal Information Card -->
          <div class="card bg-base-100 shadow-xl app-surface-secondary">
            <div class="card-body">
              <h3 class="card-title text-lg mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Personal Information
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Full Name:</span>
                  <span>{{ profile.user_info.full_name || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Gender:</span>
                  <span>{{ profile.personal_info.gender || 'Not specified' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Date of Birth:</span>
                  <span>{{ formatDate(profile.personal_info.date_of_birth) || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Phone:</span>
                  <span>{{ profile.personal_info.phone_number || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="font-medium">Alternative Phone:</span>
                  <span>{{ profile.personal_info.alternative_phone || 'Not provided' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Address Card -->
          <div class="card bg-base-100 shadow-xl app-surface-secondary">
            <div class="card-body">
              <h3 class="card-title text-lg mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Address
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Barangay:</span>
                  <span>{{ profile.address.barangay || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">City:</span>
                  <span>{{ profile.address.city || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Province:</span>
                  <span>{{ profile.address.province || 'Not provided' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Country:</span>
                  <span>{{ profile.address.country || 'Philippines' }}</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="font-medium">Postal Code:</span>
                  <span>{{ profile.address.postal_code || 'Not provided' }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Academic Information Card -->
          <div class="card bg-base-100 shadow-xl app-surface-secondary">
            <div class="card-body">
              <h3 class="card-title text-lg mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                Academic Information
              </h3>
              <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Program:</span>
                  <span>{{ profile.academic_info.program_name || 'Not specified' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Department:</span>
                  <span>{{ profile.academic_info.department || 'Not specified' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Batch:</span>
                  <span>{{ profile.academic_info.batch_name || 'Not specified' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Year Graduated:</span>
                  <span>{{ profile.academic_info.year_graduated || 'Not specified' }}</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="font-medium">GPA:</span>
                  <span>{{ profile.academic_info.gpa || 'Not provided' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Employment Status Card -->
          <div class="card bg-base-100 shadow-xl app-surface-secondary">
            <div class="card-body">
              <div class="flex justify-between items-center mb-4">
                <h3 class="card-title text-lg flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6" />
                  </svg>
                  Employment Status
                </h3>
                <button 
                  @click="refreshEmploymentData" 
                  class="btn btn-sm btn-ghost"
                  title="Refresh employment data">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="{ 'animate-spin': loading }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </button>
              </div>
              <div v-if="employmentData" class="space-y-3">
                <div class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Status:</span>
                  <span class="badge badge-primary">{{ formatEmploymentStatus(employmentData.employment_status) || 'Not specified' }}</span>
                </div>
                <div v-if="employmentData.company_name" class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Company:</span>
                  <span>{{ employmentData.company_name }}</span>
                </div>
                <div v-if="employmentData.job_title" class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Position:</span>
                  <span>{{ employmentData.job_title }}</span>
                </div>
                <div v-if="employmentData.employment_type" class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Employment Type:</span>
                  <span>{{ formatEmploymentType(employmentData.employment_type) }}</span>
                </div>
                <div v-if="employmentData.work_classification" class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Work Classification:</span>
                  <span>{{ employmentData.work_classification }}</span>
                </div>
                <div v-if="employmentData.salary_range" class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Salary Range:</span>
                  <span>{{ employmentData.salary_range }}</span>
                </div>
                <div v-if="employmentData.date_employed" class="flex justify-between py-2 border-b border-base-300">
                  <span class="font-medium">Date Employed:</span>
                  <span>{{ formatDate(employmentData.date_employed) }}</span>
                </div>
                <div v-if="employmentData.submitted_at" class="text-xs opacity-70 bg-base-200 p-3 rounded-lg mt-3">
                  Last updated: {{ formatDate(employmentData.submitted_at) }}
                </div>
              </div>
              <div v-else class="text-center py-6">
                <div class="opacity-50 mb-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <p class="mb-2">No employment information available.</p>
                <p class="text-sm opacity-70 mb-4">Complete the employment tracer form to update this section.</p>
                <button @click="navigateToTracerForm" class="btn btn-primary-custom">
                  Complete Tracer Form
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template><script setup>
import Layout from "@/components/layout/Layout.vue";
import { useRouter } from "vue-router";
import { ref, onMounted, computed } from "vue";
import alumniService from "@/services/alumni.js";

const router = useRouter();
const profile = ref(null);
const employmentData = ref(null);
const loading = ref(true);
const error = ref(null);
const imageError = ref(false);

const handleLogout = () => {
  router.push("/home");
};

// Computed property for real-time profile completion calculation
const profileCompletionPercentage = computed(() => {
  if (!profile.value) return 0;
  
  const fields = [
    // Personal Information
    profile.value.personal_info?.phone_number,
    profile.value.personal_info?.alternative_phone,
    profile.value.personal_info?.date_of_birth,
    profile.value.personal_info?.gender,
    // Address Information
    profile.value.address?.barangay,
    profile.value.address?.city,
    profile.value.address?.province,
    profile.value.address?.country,
    profile.value.address?.postal_code,
    // Academic Information
    profile.value.academic_info?.graduation_date,
    profile.value.academic_info?.gpa,
    // Social Links
    profile.value.social_links?.linkedin_profile,
    profile.value.social_links?.facebook_profile
  ];
  
  const completedFields = fields.filter(field => field && field.toString().trim() !== '').length;
  
  // Add bonus for employment information
  const hasEmploymentInfo = employmentData.value && (
    employmentData.value.employment_status ||
    employmentData.value.company_name ||
    employmentData.value.job_title
  );
  
  const totalFields = fields.length + (hasEmploymentInfo ? 1 : 0);
  const completed = completedFields + (hasEmploymentInfo ? 1 : 0);
  
  return Math.round((completed / (fields.length + 1)) * 100);
});

const navigateToSettings = async () => {
  try {
    console.log('Navigating to alumni settings...');
    
    // Check user authentication
    const userStr = localStorage.getItem("user");
    console.log('User data:', userStr);
    
    if (userStr) {
      const userData = JSON.parse(userStr);
      console.log('User role:', userData.role);
    }
    
    await router.push('/alumni_settings');
    console.log('Navigation to settings successful');
  } catch (error) {
    console.error('Error navigating to settings:', error);
    // Fallback: try with query parameters to force route change
    await router.push({ path: '/alumni_settings', query: { t: Date.now() } });
  }
};

const navigateToTracerForm = async () => {
  try {
    console.log('Navigating to tracer form...');
    
    // Check user authentication
    const userStr = localStorage.getItem("user");
    console.log('User data:', userStr);
    
    if (userStr) {
      const userData = JSON.parse(userStr);
      console.log('User role:', userData.role);
    }
    
    await router.push('/alumni_tracer_form');
    console.log('Navigation to tracer form successful');
  } catch (error) {
    console.error('Error navigating to tracer form:', error);
    // Fallback: try with query parameters to force route change
    await router.push({ path: '/alumni_tracer_form', query: { t: Date.now() } });
  }
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

const fetchEmploymentData = async () => {
  try {
    const employmentResponse = await alumniService.getEmploymentStatus();
    
    if (employmentResponse.success && employmentResponse.employment) {
      employmentData.value = employmentResponse.employment;
    } else {
      employmentData.value = null;
    }
  } catch (err) {
    console.error('Error fetching employment data:', err);
    employmentData.value = null;
  }
};

const refreshEmploymentData = async () => {
  await fetchEmploymentData();
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

onMounted(async () => {
  await fetchProfile();
  await fetchEmploymentData();
});
</script>
