<template>
  <div class="card app-surface shadow p-3 sm:p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-3">
      <h3 class="font-semibold text-lg flex items-center gap-2">
        <IconFileText :size="20" />
        Employment Tracer Forms
      </h3>
      <div class="text-sm text-gray-500 flex items-center gap-2">
        <IconBarChart3 :size="16" />
        Total: {{ forms.length }} forms
      </div>
    </div>

    <!-- Responsive Table with Horizontal Scroll -->
    <div class="overflow-x-auto" style="overflow-y: visible;">
      <div style="position: relative; z-index: 1;">
        <table class="table table-zebra w-full min-w-[800px]">
          <thead>
            <tr>
              <th class="w-20">Year</th>
              <th class="min-w-[200px]">Form Title</th>
              <th class="w-24">Status</th>
              <th class="w-32">Questions</th>
              <th class="w-24">Responses</th>
              <th class="w-20">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in forms" :key="item.form_id" class="hover">
              <td>
                <div class="flex items-center gap-2">
                  <span class="font-semibold">{{ item.form_year }}</span>
                  <span
                    v-if="item.is_active"
                    class="badge badge-primary badge-xs"
                    >Active</span
                  >
                </div>
              </td>
              <td>
                <div class="flex flex-col">
                  <span class="font-medium text-text">{{ item.form_title }}</span>
                  <span
                    v-if="item.form_description"
                    class="text-xs text-text-muted truncate max-w-xs"
                  >
                    {{ item.form_description }}
                  </span>
                </div>
              </td>
              <td>
                <span
                  v-if="item.is_active"
                  class="badge badge-success gap-2"
                >
                  <IconCheck :size="12" />
                  Active
                </span>
                <span v-else class="badge badge-ghost text-text">Inactive</span>
              </td>
              <td>
                <div class="flex flex-col text-sm">
                  <div class="flex items-center gap-1 mb-1">
                    <IconUsers :size="12" />
                    <span class="badge badge-outline badge-xs">
                      {{ getAdditionalQuestionsCount(item) }}
                    </span>
                    <span class="text-xs opacity-60">custom</span>
                  </div>
                  <div class="text-xs opacity-60">
                    + {{ getEmploymentQuestionsCount(item) }} employment
                  </div>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-2">
                  <IconBarChart3 :size="14" />
                  <span class="badge badge-info badge-sm">
                    {{ responseCounts[item.form_id] || 0 }}
                  </span>
                  <button
                    v-if="responseCounts[item.form_id] > 0"
                    @click="$emit('viewResponses', item)"
                    class="btn btn-xs btn-ghost text-blue-500 hover:bg-blue-500 hover:text-white"
                  >
                    View
                  </button>
                </div>
              </td>
              <td class="relative overflow-visible">
                <!-- replaced inline dropdown with teleport-based dropdown -->
                <div
                  tabindex="0"
                  role="button"
                  class="btn btn-ghost btn-sm btn-circle"
                  @click.stop="toggleDropdown(item, $event)"
                  :ref="el => setTriggerRef(item.form_id, el)"
                >
                  <IconEllipsisV :size="16" />
                </div>

                <!-- Teleport dropdown into body so it's not clipped by overflow parent -->
                <teleport to="body">
                  <ul
                    v-if="openDropdownId === item.form_id"
                    class="menu p-1 shadow-lg bg-base-100 rounded-box w-36 border border-base-300"
                    :style="{
                      position: 'fixed',
                      top: dropdownPos.top + 'px',
                      left: dropdownPos.left + 'px',
                      zIndex: 99999
                    }"
                    @click.stop
                  >
                    <li>
                      <a @click="$emit('viewForm', item)" class="flex items-center gap-2 text-xs py-1">
                        <IconEye :size="14" /> Preview
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('loadForm', item)" class="flex items-center gap-2 text-xs py-1">
                        <IconEdit :size="14" /> Edit
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('viewResponses', item)" class="flex items-center gap-2 text-xs py-1">
                        <IconBarChart3 :size="14" /> Responses
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('duplicateForm', item)" class="flex items-center gap-2 text-xs py-1">
                        <IconFileText :size="14" /> Duplicate
                      </a>
                    </li>
                    <li v-if="!item.is_active">
                      <a @click="$emit('activateForm', item)" class="flex items-center gap-2 text-success text-xs py-1">
                        <IconCheck :size="14" /> Activate
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('deleteForm', item)" class="flex items-center gap-2 text-error text-xs py-1">
                        <IconTrash2 :size="14" /> Delete
                      </a>
                    </li>
                  </ul>
                </teleport>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="forms.length === 0" class="text-center py-12">
        <IconFileText :size="48" class="mx-auto text-gray-400 mb-4" />
        <div class="text-lg font-medium text-text mb-2">
          No Employment Tracer Forms Yet
        </div>
        <div class="text-sm text-text-muted mb-4">
          Create your first employment tracer form to start collecting
          alumni employment data.
        </div>
        <button
          class="btn app-primary text-white flex items-center gap-2 mx-auto"
          @click="$emit('createForm')"
        >
          <IconPlus :size="16" />
          Create Your First Employment Tracer
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  forms: {
    type: Array,
    required: true
  },
  responseCounts: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'viewForm',
  'loadForm', 
  'duplicateForm', 
  'activateForm', 
  'deleteForm', 
  'viewResponses',
  'createForm'
])

// Dropdown handling
const openDropdownId = ref(null)
const dropdownPos = ref({ top: 0, left: 0 })
const triggerRefs = new Map()

function setTriggerRef(id, el) {
  if (el) triggerRefs.set(id, el)
  else triggerRefs.delete(id)
}

function toggleDropdown(item, event) {
  if (openDropdownId.value === item.form_id) {
    closeDropdown()
    return
  }
  openDropdownId.value = item.form_id
  // Position relative to trigger button
  const btn = triggerRefs.get(item.form_id) || event.currentTarget
  const rect = btn.getBoundingClientRect()
  // Adjust left so menu aligns to right edge like dropdown-end
  const menuWidth = 144 // approximate menu width (w-36 = 9rem = 144px)
  dropdownPos.value.top = rect.bottom + window.scrollY
  dropdownPos.value.left = rect.right + window.scrollX - menuWidth
}

function closeDropdown() {
  openDropdownId.value = null
}

function onDocClick(e) {
  // Close when clicking outside
  if (!e.target.closest('.menu')) closeDropdown()
}

function onEsc(e) {
  if (e.key === 'Escape') closeDropdown()
}

// Helper functions for parsing form questions in the table
const getAdditionalQuestionsCount = (item) => {
  if (!item.questions) return 0
  if (typeof item.questions === 'string') {
    try {
      const parsed = JSON.parse(item.questions)
      return Array.isArray(parsed) ? parsed.length : 0
    } catch (e) {
      return 0
    }
  }
  return Array.isArray(item.questions) ? item.questions.length : 0
}

const getEmploymentQuestionsCount = (item) => {
  if (!item.selected_employment_questions) return 0
  if (typeof item.selected_employment_questions === 'string') {
    try {
      const parsed = JSON.parse(item.selected_employment_questions)
      return Array.isArray(parsed) ? parsed.length : 0
    } catch (e) {
      return 0
    }
  }
  return Array.isArray(item.selected_employment_questions) 
    ? item.selected_employment_questions.length 
    : 0
}

onMounted(() => {
  document.addEventListener('click', onDocClick)
  document.addEventListener('keydown', onEsc)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocClick)
  document.removeEventListener('keydown', onEsc)
})
</script>
