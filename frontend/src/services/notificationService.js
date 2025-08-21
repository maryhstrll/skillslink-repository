const BASE_URL = 'http://localhost/skillslink/backend';

export const notificationService = {
  /**
   * Get all notifications for the current user
   */
  async getNotifications() {
    try {
      const response = await fetch(`${BASE_URL}/api/notifications.php?action=get`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        }
      });

      const result = await response.json();
      
      if (result.success) {
        return {
          success: true,
          data: result.notifications || []
        };
      } else {
        return {
          success: false,
          error: result.message || 'Failed to fetch notifications'
        };
      }
    } catch (error) {
      console.error('Error fetching notifications:', error);
      return {
        success: false,
        error: 'Network error occurred'
      };
    }
  },

  /**
   * Get unread notification count
   */
  async getUnreadCount() {
    try {
      const response = await fetch(`${BASE_URL}/api/notifications.php?action=count`, {
        method: 'GET',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        }
      });

      const result = await response.json();
      
      if (result.success) {
        return {
          success: true,
          count: result.unread_count || 0
        };
      } else {
        return {
          success: false,
          error: result.message || 'Failed to fetch notification count'
        };
      }
    } catch (error) {
      console.error('Error fetching notification count:', error);
      return {
        success: false,
        error: 'Network error occurred'
      };
    }
  },

  /**
   * Mark a specific notification as read
   */
  async markAsRead(notificationId) {
    try {
      const response = await fetch(`${BASE_URL}/api/notifications.php?action=mark_read`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          notification_id: notificationId
        })
      });

      const result = await response.json();
      return result;
    } catch (error) {
      console.error('Error marking notification as read:', error);
      return {
        success: false,
        error: 'Network error occurred'
      };
    }
  },

  /**
   * Mark all notifications as read
   */
  async markAllAsRead() {
    try {
      const response = await fetch(`${BASE_URL}/api/notifications.php?action=mark_all_read`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        }
      });

      const result = await response.json();
      return result;
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
      return {
        success: false,
        error: 'Network error occurred'
      };
    }
  },

  /**
   * Create notifications (admin only)
   */
  async createNotification(userIds, title, message, type = 'in_app', priority = 'normal', category = 'announcement') {
    try {
      const response = await fetch(`${BASE_URL}/api/notifications.php?action=create`, {
        method: 'POST',
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          user_ids: userIds,
          type,
          title,
          message,
          priority,
          category
        })
      });

      const result = await response.json();
      return result;
    } catch (error) {
      console.error('Error creating notification:', error);
      return {
        success: false,
        error: 'Network error occurred'
      };
    }
  },

  /**
   * Format notification date for display
   */
  formatNotificationDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
      return 'Just now';
    } else if (diffInSeconds < 3600) {
      const minutes = Math.floor(diffInSeconds / 60);
      return `${minutes} minute${minutes === 1 ? '' : 's'} ago`;
    } else if (diffInSeconds < 86400) {
      const hours = Math.floor(diffInSeconds / 3600);
      return `${hours} hour${hours === 1 ? '' : 's'} ago`;
    } else if (diffInSeconds < 604800) {
      const days = Math.floor(diffInSeconds / 86400);
      return `${days} day${days === 1 ? '' : 's'} ago`;
    } else {
      return date.toLocaleDateString();
    }
  },

  /**
   * Get notification priority class for styling
   */
  getPriorityClass(priority) {
    switch (priority) {
      case 'high':
        return 'border-l-red-500 bg-red-50';
      case 'normal':
        return 'border-l-blue-500 bg-blue-50';
      case 'low':
        return 'border-l-gray-500 bg-gray-50';
      default:
        return 'border-l-gray-500 bg-gray-50';
    }
  },

  /**
   * Get notification category icon
   */
  getCategoryIcon(category) {
    switch (category) {
      case 'announcement':
        return 'fas fa-bullhorn';
      case 'reminder':
        return 'fas fa-bell';
      case 'system':
        return 'fas fa-cog';
      case 'employment_update':
        return 'fas fa-briefcase';
      default:
        return 'fas fa-info-circle';
    }
  }
};

export default notificationService;
