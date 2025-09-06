<template>
  <Layout @logout="handleLogout">
    <div class="space-y-4 sm:space-y-6 p-3 sm:p-4">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0">
        <h2 class="text-xl sm:text-2xl font-bold">
          Manage Programs Available
        </h2>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
          <button
            class="btn btn-primary flex items-center gap-2 text-sm"
            @click="openAdd()">
            <IconPlus :size="16" />
            <span class="hidden sm:inline">Add New Program</span>
            <span class="sm:hidden">Add Program</span>
          </button>
        </div>
      </div>

      <div class="space-y-4 sm:space-y-6">
        <!-- Programs Table -->
         <div class="card app-surface shadow p-3 sm:p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
                <h3 class="font-semibold text-lg flex items-center gap-2">
                    <IconSquareLibrary :size="20"/>
                    List of Programs
                </h3>
                <div class="text-sm text-gray-500 flex items-center gap-2">
                    <IconBarChart3 :size="16" />
                    Total: {{ }} programs
              </div>
            </div>

            <!-- Responsive Table ng Programs -->
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full min-w-[800px]">
                    <thead>
                        <tr>
                            <th class="w-20">COPR/CTPR No. (Program Code)</th>
                            <th class="w-20">Program Name</th>
                            <th class="w-20">Department (Sector)</th>
                            <th class="w-20">Duration Hours</th>
                            <th class="min-w-[200px]">Description</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
         </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref } from "vue";
import Layout from "@/components/layout/Layout.vue";
import { useRouter } from "vue-router";

const router = useRouter();

const handleLogout = () => {
  router.push("/home");
};
</script>
