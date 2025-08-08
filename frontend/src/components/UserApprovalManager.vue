<template>
  <div class="space-y-6">
    <!-- Pending Users Section -->
    <div class="card bg-base-100 shadow-xl">
      <div class="card-body">
        <div class="flex justify-between items-center mb-4">
          <h2 class="card-title">Pending Alumni Approvals</h2>
          <button 
            class="btn btn-primary btn-sm" 
            @click="refreshPendingUsers"
            :disabled="isLoading"
          >
            <i class="fas fa-refresh" :class="{ 'animate-spin': isLoading }"></i>
            Refresh
          </button>
        </div>

        <div v-if="error" class="alert alert-error shadow-lg mb-4">
          <span>{{ error }}</span>
        </div>

        <div v-if="success" class="alert alert-success shadow-lg mb-4">
          <span>{{ success }}</span>
        </div>

        <div v-if="isLoading" class="flex justify-center py-8">
          <span class="loading loading-spinner loading-lg"></span>
        </div>

        <div v-else-if="pendingUsers.length === 0" class="text-center py-8">
          <div class="text-base-content/60">
            <i class="fas fa-users text-4xl mb-4"></i>
            <p>No pending alumni approvals</p>
          </div>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="table table-zebra w-full">
            <thead>
              <tr>
                <th>Name</th>
                <th>Student ID</th>
                <th>Email</th>
                <th>Registration Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in pendingUsers" :key="user.user_id">
                <td>
                  <div class="font-bold">{{ user.first_name }} {{ user.last_name }}</div>
                </td>
                <td>
                  <div v-if="user.role === 'alumni'" class="text-sm">
                    <div><strong>{{ user.student_id }}</strong></div>
                  </div>
                  <div v-else class="text-gray-500">N/A</div>
                </td>
                <td>{{ user.email }}</td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <div class="flex gap-2">
                    <button 
                      class="btn btn-success btn-sm"
                      @click="approveUser(user.user_id)"
                      :disabled="processingUsers.has(user.user_id)"
                    >
                      <i class="fas fa-check"></i>
                      Approve
                    </button>
                    <button 
                      class="btn btn-error btn-sm"
                      @click="rejectUser(user.user_id)"
                      :disabled="processingUsers.has(user.user_id)"
                    >
                      <i class="fas fa-times"></i>
                      Reject
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import userApprovalService from '@/services/userApproval.js';

export default {
  name: 'UserApprovalManager',
  data() {
    return {
      pendingUsers: [],
      isLoading: false,
      error: '',
      success: '',
      processingUsers: new Set()
    };
  },
  async mounted() {
    await this.loadPendingUsers();
  },
  methods: {
    async loadPendingUsers() {
      this.isLoading = true;
      this.error = '';
      
      try {
        const result = await userApprovalService.getPendingUsers();
        if (result.success) {
          this.pendingUsers = result.data.users || [];
        } else {
          this.error = result.error;
        }
      } catch (error) {
        this.error = 'Failed to load pending users';
      } finally {
        this.isLoading = false;
      }
    },

    async refreshPendingUsers() {
      await this.loadPendingUsers();
      this.success = '';
    },

    async approveUser(userId) {
      this.processingUsers.add(userId);
      this.error = '';
      this.success = '';

      try {
        const result = await userApprovalService.approveUser(userId);
        if (result.success) {
          this.success = result.data.message;
          // Remove user from pending list
          this.pendingUsers = this.pendingUsers.filter(user => user.user_id !== userId);
        } else {
          this.error = result.error;
        }
      } catch (error) {
        this.error = 'Failed to approve user';
      } finally {
        this.processingUsers.delete(userId);
      }
    },

    async rejectUser(userId) {
      this.processingUsers.add(userId);
      this.error = '';
      this.success = '';

      try {
        const result = await userApprovalService.rejectUser(userId);
        if (result.success) {
          this.success = result.data.message;
          // Remove user from pending list
          this.pendingUsers = this.pendingUsers.filter(user => user.user_id !== userId);
        } else {
          this.error = result.error;
        }
      } catch (error) {
        this.error = 'Failed to reject user';
      } finally {
        this.processingUsers.delete(userId);
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  }
};
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
