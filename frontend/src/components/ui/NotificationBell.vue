<template>
  <div class="relative" ref="dropdownRef">
    <!-- Notification Bell Button -->
    <button 
      @click="toggleDropdown"
      class="btn btn-ghost btn-circle indicator"
      :class="{ 'bg-base-300': isOpen }"
    >
      <i class="fas fa-bell text-lg"></i>
      <span v-if="unreadCount > 0" class="badge badge-sm indicator-item badge-error">
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>
    
    <!-- Notification Dropdown -->
    <div 
      v-show="isOpen"
      class="notification-dropdown-fixed absolute right-0 top-full mt-2 bg-white border border-gray-300 rounded-lg shadow-xl w-80"
      @click.stop
    >
      <div class="p-4">
        <!-- Header -->
        <div class="flex justify-between items-center mb-3 border-b pb-2">
          <h3 class="font-bold text-lg text-gray-800">Notifications</h3>
          <div class="flex items-center gap-2">
            <button 
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="btn btn-xs btn-primary"
              :disabled="loading"
            >
              Mark all read
            </button>
            <button 
              @click="closeDropdown"
              class="btn btn-xs btn-circle btn-ghost"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center py-8">
          <span class="loading loading-spinner loading-sm"></span>
          <span class="ml-2 text-gray-600">Loading...</span>
        </div>
        
        <!-- No Notifications -->
        <div v-else-if="notifications.length === 0" class="text-center py-8 text-gray-500">
          <i class="fas fa-bell-slash text-3xl mb-2"></i>
          <p>No notifications yet</p>
        </div>
        
        <!-- Notifications List -->
        <div v-else class="max-h-80 overflow-y-auto space-y-2">
          <div 
            v-for="notification in notifications" 
            :key="notification.notification_id"
            class="p-3 rounded-lg border-l-4 cursor-pointer hover:bg-gray-50 transition-all duration-200"
            :class="[
              getPriorityClass(notification.priority),
              notification.status === 'read' ? 'opacity-60' : ''
            ]"
            @click="handleNotificationClick(notification)"
          >
            <!-- Notification Header -->
            <div class="flex items-start justify-between mb-1">
              <div class="flex items-center">
                <i :class="getCategoryIcon(notification.category)" class="mr-2 text-sm text-blue-600"></i>
                <h4 class="font-semibold text-sm text-gray-800">{{ notification.title }}</h4>
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
            
            <!-- Notification Message -->
            <p class="text-sm text-gray-700 leading-tight">{{ notification.message }}</p>
            
            <!-- Priority Badge -->
            <div class="mt-2" v-if="notification.priority === 'high'">
              <span class="badge badge-xs badge-error">High Priority</span>
            </div>
          </div>
        </div>
        
        <!-- View All Link -->
        <div v-if="notifications.length > 0" class="text-center mt-4 pt-3 border-t border-gray-200">
          <router-link 
            to="/alumni/notifications" 
            class="btn btn-sm btn-outline btn-primary w-full"
            @click="closeDropdown"
          >
            View All Notifications
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import notificationService from '@/services/notificationService.js';
import { messageService } from '@/services/messageService.js';

// State
const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(false);
const isOpen = ref(false);
const dropdownRef = ref(null);

// Polling interval for real-time updates
let pollInterval = null;

// Methods
const loadNotifications = async () => {
  loading.value = true;
  try {
    const result = await notificationService.getNotifications();
    if (result.success) {
      notifications.value = result.data.slice(0, 5); // Show only latest 5 in dropdown
      await updateUnreadCount();
    } else {
      console.error('Failed to load notifications:', result.error);
      messageService.showMessage('Failed to load notifications: ' + result.error, 'error', 3000);
    }
  } catch (error) {
    console.error('Error loading notifications:', error);
    messageService.showMessage('Error loading notifications', 'error', 3000);
  } finally {
    loading.value = false;
  }
};

const updateUnreadCount = async () => {
  try {
    const result = await notificationService.getUnreadCount();
    if (result.success) {
      unreadCount.value = result.count;
    }
  } catch (error) {
    console.error('Error updating unread count:', error);
  }
};

const toggleDropdown = (event) => {
  event.stopPropagation();
  console.log('Toggling dropdown, current state:', isOpen.value);
  isOpen.value = !isOpen.value;
  console.log('New state:', isOpen.value);
  if (isOpen.value) {
    loadNotifications();
  }
};

const closeDropdown = () => {
  console.log('Closing dropdown');
  isOpen.value = false;
};

const handleNotificationClick = async (notification) => {
  // Mark as read if not already read
  if (notification.status !== 'read') {
    const result = await notificationService.markAsRead(notification.notification_id);
    if (result.success) {
      notification.status = 'read';
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  }
  
  // Handle notification actions based on category
  if (notification.category === 'announcement' && notification.title.includes('Tracer Form')) {
    // Navigate to tracer forms or show details
    closeDropdown();
    messageService.showMessage(
      'Please check your dashboard for the new tracer form.', 
      'info', 
      5000
    );
  }
  
  closeDropdown();
};

const markAllAsRead = async () => {
  if (loading.value) return;
  
  loading.value = true;
  try {
    const result = await notificationService.markAllAsRead();
    if (result.success) {
      notifications.value.forEach(notification => {
        notification.status = 'read';
      });
      unreadCount.value = 0;
      messageService.showMessage('All notifications marked as read', 'success');
    } else {
      messageService.showMessage('Failed to mark notifications as read', 'error');
    }
  } catch (error) {
    console.error('Error marking all as read:', error);
    messageService.showMessage('An error occurred', 'error');
  } finally {
    loading.value = false;
  }
};

const getPriorityClass = (priority) => {
  return notificationService.getPriorityClass(priority);
};

const getCategoryIcon = (category) => {
  return notificationService.getCategoryIcon(category);
};

const formatNotificationDate = (dateString) => {
  return notificationService.formatNotificationDate(dateString);
};

// Lifecycle
onMounted(() => {
  console.log('NotificationBell mounted');
  loadNotifications();
  
  // Poll for new notifications every 30 seconds
  pollInterval = setInterval(() => {
    updateUnreadCount();
  }, 30000);
  
  // Close dropdown when clicking outside
  const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
      closeDropdown();
    }
  };
  
  document.addEventListener('click', handleClickOutside);
  
  // Store the handler to remove it later
  window.notificationDropdownHandler = handleClickOutside;
});

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
  }
  
  // Remove the click outside listener
  if (window.notificationDropdownHandler) {
    document.removeEventListener('click', window.notificationDropdownHandler);
    delete window.notificationDropdownHandler;
  }
});
</script>

<style scoped>
/* Custom scrollbar for notifications list */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Force the dropdown to show above navbar */
.notification-dropdown-fixed {
  position: absolute !important;
  z-index: 99999 !important;
}

/* Force positioning above everything */
.absolute {
  position: absolute !important;
}
</style>
