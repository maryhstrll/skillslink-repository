<template>

<!-- Recent Activity -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">
            <i class="fas fa-clock"></i>
            Recent Activity
          </h2>
          <div class="overflow-x-auto">
            <table class="table table-zebra">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Action</th>
                  <th>Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="activity in recentActivities" :key="activity.id">
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar placeholder">
                        <div class="bg-neutral text-neutral-content rounded-full w-8">
                          <span class="text-xs">{{ activity.userInitials }}</span>
                        </div>
                      </div>
                      <span>{{ activity.user }}</span>
                    </div>
                  </td>
                  <td>{{ activity.action }}</td>
                  <td>{{ activity.time }}</td>
                  <td>
                    <div class="badge" :class="activity.statusClass">{{ activity.status }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
</template>

<script setup>
import { ref } from 'vue';

// Recent activities
const recentActivities = ref([])

</script>