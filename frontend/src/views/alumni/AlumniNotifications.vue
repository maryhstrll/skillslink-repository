<template>
  <Layout>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-primary">Notifications</h1>
        <div class="flex gap-2">
          <button 
            @click="markAllAsRead"
            :disabled="loading || unreadCount === 0"
            class="btn btn-sm btn-primary"
          >
            <i class="fas fa-check-double mr-2"></i>
            Mark All Read
          </button>
          <button 
            @click="refreshNotifications"
            :disabled="loading"
            class="btn btn-sm btn-outline"
          >
            <i class="fas fa-refresh mr-2" :class="{ 'animate-spin': loading }"></i>
            Refresh
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats shadow">
        <div class="stat">
          <div class="stat-figure text-primary">
            <i class="fas fa-bell text-2xl"></i>
          </div>
          <div class="stat-title">Total Notifications</div>
          <div class="stat-value">{{ notifications.length }}</div>
          <div class="stat-desc">All time</div>
        </div>
        
        <div class="stat">
          <div class="stat-figure text-secondary">
            <i class="fas fa-envelope text-2xl"></i>
          </div>
          <div class="stat-title">Unread</div>
          <div class="stat-value text-secondary">{{ unreadCount }}</div>
          <div class="stat-desc">{{ unreadCount === 1 ? 'notification' : 'notifications' }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <div class="flex flex-wrap gap-4 items-center">
            <div class="form-control">
              <label class="label">
                <span class="label-text">Filter by Status</span>
              </label>
              <select v-model="statusFilter" class="select select-bordered select-sm">
                <option value="all">All</option>
                <option value="unread">Unread Only</option>
                <option value="read">Read Only</option>
              </select>
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text">Filter by Category</span>
              </label>
              <select v-model="categoryFilter" class="select select-bordered select-sm">
                <option value="all">All Categories</option>
                <option value="announcement">Announcements</option>
                <option value="reminder">Reminders</option>
                <option value="system">System</option>
                <option value="employment_update">Employment Updates</option>
              </select>
            </div>
            
            <div class="form-control">
              <label class="label">
                <span class="label-text">Filter by Priority</span>
              </label>
              <select v-model="priorityFilter" class="select select-bordered select-sm">
                <option value="all">All Priorities</option>
                <option value="high">High Priority</option>
                <option value="normal">Normal Priority</option>
                <option value="low">Low Priority</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center py-8">
            <span class="loading loading-spinner loading-lg"></span>
            <span class="ml-4 text-lg">Loading notifications...</span>
          </div>

          <!-- No Notifications -->
          <div v-else-if="filteredNotifications.length === 0" class="text-center py-12">
            <i class="fas fa-bell-slash text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-500 mb-2">No notifications found</h3>
            <p class="text-gray-400">
              {{ notifications.length === 0 ? 'You have no notifications yet.' : 'No notifications match your current filters.' }}
            </p>
          </div>

          <!-- Notifications List -->
          <div v-else class="space-y-3">
            <div 
              v-for="notification in filteredNotifications" 
              :key="notification.notification_id"
              class="p-4 rounded-lg border-l-4 cursor-pointer hover:shadow-md transition-all duration-200"
              :class="[
                getPriorityClass(notification.priority),
                notification.status === 'read' ? 'opacity-70' : 'shadow-sm'
              ]"
              @click="handleNotificationClick(notification)"
            >
              <!-- Notification Header -->
              <div class="flex items-start justify-between mb-2">
                <div class="flex items-center flex-1">
                  <i :class="getCategoryIcon(notification.category)" class="mr-3 text-lg"></i>
                  <div class="flex-1">
                    <h4 class="font-semibold text-base">{{ notification.title }}</h4>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="badge badge-sm" :class="getCategoryBadgeClass(notification.category)">
                        {{ formatCategory(notification.category) }}
                      </span>
                      <span class="badge badge-sm" :class="getPriorityBadgeClass(notification.priority)">
                        {{ formatPriority(notification.priority) }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="flex items-center ml-4">
                  <span 
                    v-if="notification.status !== 'read'" 
                    class="w-3 h-3 bg-blue-500 rounded-full mr-3"
                  ></span>
                  <div class="text-right">
                    <div class="text-sm text-gray-500">
                      {{ formatNotificationDate(notification.created_at) }}
                    </div>
                    <div class="text-xs text-gray-400">
                      {{ formatFullDate(notification.created_at) }}
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Notification Message -->
              <p class="text-gray-700 leading-relaxed pl-8">{{ notification.message }}</p>
              
              <!-- Status Indicator -->
              <div class="flex justify-between items-center mt-3 pl-8">
                <div class="flex items-center gap-2">
                  <span class="text-xs text-gray-500">Status:</span>
                  <span 
                    class="badge badge-xs"
                    :class="notification.status === 'read' ? 'badge-success' : 'badge-warning'"
                  >
                    {{ notification.status === 'read' ? 'Read' : 'Unread' }}
                  </span>
                  <span v-if="notification.read_at" class="text-xs text-gray-400">
                    • Read {{ formatNotificationDate(notification.read_at) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Layout from '@/components/layout/Layout.vue';
import notificationService from '@/services/notificationService.js';
import { messageService } from '@/services/messageService.js';

// State
const notifications = ref([]);
const loading = ref(false);
const unreadCount = ref(0);

// Filters
const statusFilter = ref('all');
const categoryFilter = ref('all');
const priorityFilter = ref('all');

// Computed
const filteredNotifications = computed(() => {
  let filtered = [...notifications.value];
  
  // Status filter
  if (statusFilter.value === 'read') {
    filtered = filtered.filter(n => n.status === 'read');
  } else if (statusFilter.value === 'unread') {
    filtered = filtered.filter(n => n.status !== 'read');
  }
  
  // Category filter
  if (categoryFilter.value !== 'all') {
    filtered = filtered.filter(n => n.category === categoryFilter.value);
  }
  
  // Priority filter
  if (priorityFilter.value !== 'all') {
    filtered = filtered.filter(n => n.priority === priorityFilter.value);
  }
  
  return filtered;
});

// Methods
const loadNotifications = async () => {
  loading.value = true;
  try {
    const result = await notificationService.getNotifications();
    if (result.success) {
      notifications.value = result.data || [];
      await updateUnreadCount();
    } else {
      messageService.showMessage('Failed to load notifications: ' + result.error, 'error');
    }
  } catch (error) {
    console.error('Error loading notifications:', error);
    messageService.showMessage('An error occurred while loading notifications', 'error');
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

const refreshNotifications = async () => {
  await loadNotifications();
  messageService.showMessage('Notifications refreshed', 'success');
};

const handleNotificationClick = async (notification) => {
  // Mark as read if not already read
  if (notification.status !== 'read') {
    const result = await notificationService.markAsRead(notification.notification_id);
    if (result.success) {
      notification.status = 'read';
      notification.read_at = new Date().toISOString();
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    }
  }
};

const markAllAsRead = async () => {
  if (loading.value || unreadCount.value === 0) return;
  
  loading.value = true;
  try {
    const result = await notificationService.markAllAsRead();
    if (result.success) {
      notifications.value.forEach(notification => {
        if (notification.status !== 'read') {
          notification.status = 'read';
          notification.read_at = new Date().toISOString();
        }
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

// Utility methods
const getPriorityClass = (priority) => {
  return notificationService.getPriorityClass(priority);
};

const getCategoryIcon = (category) => {
  return notificationService.getCategoryIcon(category);
};

const formatNotificationDate = (dateString) => {
  return notificationService.formatNotificationDate(dateString);
};

const formatFullDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString();
};

const getCategoryBadgeClass = (category) => {
  switch (category) {
    case 'announcement': return 'badge-info';
    case 'reminder': return 'badge-warning';
    case 'system': return 'badge-neutral';
    case 'employment_update': return 'badge-success';
    default: return 'badge-ghost';
  }
};

const getPriorityBadgeClass = (priority) => {
  switch (priority) {
    case 'high': return 'badge-error';
    case 'normal': return 'badge-primary';
    case 'low': return 'badge-ghost';
    default: return 'badge-ghost';
  }
};

const formatCategory = (category) => {
  return category.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const formatPriority = (priority) => {
  return priority.charAt(0).toUpperCase() + priority.slice(1);
};

// Lifecycle
onMounted(() => {
  loadNotifications();
});
</script>

<style scoped>
/* Custom hover effects */
.cursor-pointer:hover {
  transform: translateY(-1px);
}

/* Badge transitions */
.badge {
  transition: all 0.2s ease;
}
</style>
