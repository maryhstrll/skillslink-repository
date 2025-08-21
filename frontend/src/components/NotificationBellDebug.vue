<template>
  <div class="relative">
    <!-- Simple Debug Bell -->
    <button 
      @click="handleClick"
      class="btn btn-ghost btn-circle indicator"
      style="background-color: #f0f0f0; border: 2px solid #333;"
    >
      <i class="fas fa-bell text-lg"></i>
      <span v-if="unreadCount > 0" class="badge badge-sm indicator-item badge-error">
        {{ unreadCount }}
      </span>
    </button>
    
    <!-- Debug Info -->
    <div v-if="showDebug" class="absolute top-full right-0 mt-2 p-4 bg-white border border-gray-300 rounded shadow-lg z-50 min-w-[300px]">
      <h3 class="font-bold mb-2">Debug Info</h3>
      <p>Is Open: {{ isOpen }}</p>
      <p>Loading: {{ loading }}</p>
      <p>Unread Count: {{ unreadCount }}</p>
      <p>Notifications Count: {{ notifications.length }}</p>
      <p>API Base URL: {{ baseUrl }}</p>
      
      <div class="mt-2">
        <button @click="testAPI" class="btn btn-xs btn-primary mr-2">Test API</button>
        <button @click="closeDebug" class="btn btn-xs btn-secondary">Close</button>
      </div>
      
      <div v-if="apiTestResult" class="mt-2 p-2 bg-gray-100 rounded text-xs">
        <pre>{{ JSON.stringify(apiTestResult, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import notificationService from '@/services/notificationService.js';

const isOpen = ref(false);
const loading = ref(false);
const unreadCount = ref(0);
const notifications = ref([]);
const showDebug = ref(false);
const apiTestResult = ref(null);
const baseUrl = 'http://localhost/skillslink/backend';

const handleClick = () => {
  console.log('Notification bell clicked!');
  showDebug.value = !showDebug.value;
};

const closeDebug = () => {
  showDebug.value = false;
};

const testAPI = async () => {
  console.log('Testing API...');
  loading.value = true;
  
  try {
    const result = await notificationService.getUnreadCount();
    console.log('API Test Result:', result);
    apiTestResult.value = result;
    
    if (result.success) {
      unreadCount.value = result.count || 0;
    }
  } catch (error) {
    console.error('API Test Error:', error);
    apiTestResult.value = { error: error.message };
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  console.log('Debug NotificationBell mounted');
  testAPI();
});
</script>
