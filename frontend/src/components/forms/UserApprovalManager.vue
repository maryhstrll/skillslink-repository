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
            <IconRefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
            Refresh
          </button>
        </div>

        <div v-if="error" class="alert alert-error shadow-lg mb-4">
          <span>{{ error }}</span>
        </div>

        <div v-if="success" class="alert alert-success shadow-lg mb-4">
          <span>{{ success }}</span>
        </div>

        <DataTable
          title="Pending Alumni Approvals"
          :title-icon="IconUsers"
          :count-icon="IconUserCheck"
          :data="pendingUsers"
          :columns="tableColumns"
          :loading="isLoading"
          item-label="pending approvals"
          empty-title="No pending alumni approvals"
          empty-description="All alumni registration requests have been processed"
          :empty-icon="IconUsers"
          key-field="user_id"
          loading-text="Loading pending users..."
          :show-count="true"
        >
          <!-- Custom cell for name -->
          <template #cell-name="{ item }">
            <div class="font-bold" style="color: var(--color-text-primary);">
              {{ item.first_name }}{{ item.middle_name ? ' ' + item.middle_name : '' }} {{ item.last_name }}
            </div>
          </template>

          <!-- Custom cell for student ID -->
          <template #cell-student_id="{ item }">
            <div v-if="item.role === 'alumni'" class="text-sm">
              <div class="font-mono font-bold" style="color: var(--color-primary);">{{ item.student_id }}</div>
            </div>
            <div v-else class="text-gray-500">N/A</div>
          </template>

          <!-- Custom cell for birthdate -->
          <template #cell-birthdate="{ item }">
            <span class="text-sm">{{ item.birthdate ? formatBirthdate(item.birthdate) : 'N/A' }}</span>
          </template>

          <!-- Custom cell for gender -->
          <template #cell-gender="{ item }">
            <div v-if="item.gender" class="badge badge-ghost badge-sm">
              {{ item.gender.charAt(0).toUpperCase() + item.gender.slice(1) }}
            </div>
            <span v-else class="text-gray-500">N/A</span>
          </template>

          <!-- Custom cell for email -->
          <template #cell-email="{ value }">
            <span class="text-sm">{{ value }}</span>
          </template>

          <!-- Custom cell for registration date -->
          <template #cell-created_at="{ value }">
            <span class="text-sm opacity-75">{{ formatDate(value) }}</span>
          </template>

          <!-- Custom cell for actions -->
          <template #cell-actions="{ item }">
            <div class="flex gap-2">
              <button 
                class="btn btn-accent-custom btn-sm shadow-lg hover:shadow-xl transition-all duration-200"
                @click="approveUser(item.user_id)"
                :disabled="processingUsers.has(item.user_id)"
                title="Approve User"
              >
                <IconCheck class="w-4 h-4" />
                Approve
              </button>
              <button 
                class="btn btn-danger-custom btn-sm shadow-lg hover:shadow-xl transition-all duration-200"
                @click="rejectUser(item.user_id)"
                :disabled="processingUsers.has(item.user_id)"
                title="Reject User"
              >
                <IconX class="w-4 h-4" />
                Reject
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </div>
  </div>
</template>

<script>
import DataTable from '@/components/tables/DataTable.vue';
import userApprovalService from '@/services/userApproval.js';
import { 
  RefreshCw as IconRefreshCw, 
  Users as IconUsers, 
  Check as IconCheck, 
  X as IconX,
  UserCheck as IconUserCheck 
} from 'lucide-vue-next';

export default {
  name: 'UserApprovalManager',
  components: {
    DataTable,
    IconRefreshCw,
    IconUsers,
    IconCheck,
    IconX,
    IconUserCheck
  },
  data() {
    return {
      pendingUsers: [],
      isLoading: false,
      error: '',
      success: '',
      processingUsers: new Set()
    };
  },
  computed: {
    tableColumns() {
      return [
        {
          key: 'name',
          title: 'Name',
          headerClass: 'min-w-[200px]'
        },
        {
          key: 'student_id',
          title: 'Student ID',
          headerClass: 'w-32'
        },
        {
          key: 'birthdate',
          title: 'Birthdate',
          headerClass: 'w-32'
        },
        {
          key: 'gender',
          title: 'Gender',
          headerClass: 'w-24'
        },
        {
          key: 'email',
          title: 'Email',
          headerClass: 'min-w-[180px]'
        },
        {
          key: 'created_at',
          title: 'Registration Date',
          headerClass: 'w-36'
        },
        {
          key: 'actions',
          title: 'Actions',
          headerClass: 'w-40'
        }
      ];
    }
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
    },

    formatBirthdate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
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
