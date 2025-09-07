<template>
  <div class="card app-surface shadow-lg p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-3">
      <h3 class="font-semibold text-xl flex items-center gap-2" style="color: var(--color-text-primary);">
        <IconFileText class="w-6 h-6" />
        Employment Tracer Forms
      </h3>
      <div class="text-sm flex items-center gap-2" style="color: var(--color-text-secondary);">
        <IconBarChart3 class="w-4 h-4" />
        Total: {{ forms.length }} forms
      </div>
    </div>

    <!-- Responsive Table with Horizontal Scroll -->
    <div class="overflow-x-auto rounded-lg border app-border" style="overflow-y: visible;">
      <div style="position: relative; z-index: 1;">
        <table class="table w-full min-w-[800px]">
          <thead>
            <tr>
              <th class="w-20 font-semibold" style="color: var(--color-text-primary);">Year</th>
              <th class="min-w-[200px] font-semibold" style="color: var(--color-text-primary);">Form Title</th>
              <th class="w-24 font-semibold" style="color: var(--color-text-primary);">Status</th>
              <th class="w-32 font-semibold" style="color: var(--color-text-primary);">Questions</th>
              <th class="w-20 font-semibold" style="color: var(--color-text-primary);">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in forms" :key="item.form_id" class="hover:bg-base-200/50 transition-colors">
              <td class="py-4">
                <div class="flex items-center gap-2">
                  <span class="font-semibold" style="color: var(--color-text-primary);">{{ item.form_year }}</span>
                  <span
                    v-if="item.is_active"
                    class="badge badge-sm"
                    style="background: var(--color-accent); color: white; border: none;"
                    >Active</span
                  >
                </div>
              </td>
              <td class="py-4">
                <div class="flex flex-col">
                  <span class="font-medium" style="color: var(--color-text-primary);">{{ item.form_title }}</span>
                  <span
                    v-if="item.form_description"
                    class="text-sm truncate max-w-xs mt-1"
                    style="color: var(--color-text-secondary);"
                  >
                    {{ item.form_description }}
                  </span>
                </div>
              </td>
              <td class="py-4">
                <span
                  v-if="item.is_active"
                  class="badge gap-2"
                  style="background: var(--color-accent); color: white; border: none;"
                >
                  <IconCheck class="w-3 h-3" />
                  Active
                </span>
                <span 
                  v-else 
                  class="badge border-0"
                  style="background: var(--color-danger-light); color: var(--color-text-primary);"
                >Inactive</span>
              </td>
              <td class="py-4">
                <div class="flex flex-col text-sm gap-1">
                  <div class="flex items-center gap-2">
                    <IconUsers class="w-3 h-3" style="color: var(--color-primary);" />
                    <span class="badge badge-outline badge-sm" style="border-color: var(--color-primary); color: var(--color-primary);">
                      {{ getAdditionalQuestionsCount(item) }}
                    </span>
                    <span class="text-xs" style="color: var(--color-text-light);">custom</span>
                  </div>
                  <div class="text-xs" style="color: var(--color-text-light);">
                    + {{ getEmploymentQuestionsCount(item) }} employment
                  </div>
                </div>
              </td>
              <td class="relative overflow-visible py-4">
                <!-- replaced inline dropdown with teleport-based dropdown -->
                <div
                  tabindex="0"
                  role="button"
                  class="btn btn-ghost border-0 btn-sm btn-circle"
                  @click.stop="toggleDropdown(item, $event)"
                  :ref="el => setTriggerRef(item.form_id, el)"
                >
                  <IconEllipsisV class="w-4 h-4" />
                </div>

                <!-- Teleport dropdown into body so it's not clipped by overflow parent -->
                <teleport to="body">
                  <ul
                    v-if="openDropdownId === item.form_id"
                    class="menu p-2 shadow-lg rounded-box w-40 border"
                    style="background: var(--color-surface); border-color: var(--color-border);"
                    :style="{
                      position: 'fixed',
                      top: dropdownPos.top + 'px',
                      left: dropdownPos.left + 'px',
                      zIndex: 99999
                    }"
                    @click.stop
                  >
                    <li>
                      <a @click="$emit('viewForm', item)" class="flex items-center gap-2 text-sm py-2" style="color: var(--color-text-primary);">
                        <IconEye class="w-4 h-4" /> Preview
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('loadForm', item)" class="flex items-center gap-2 text-sm py-2" style="color: var(--color-text-primary);">
                        <IconEdit class="w-4 h-4" /> Edit
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('viewResponses', item)" class="flex items-center gap-2 text-sm py-2" style="color: var(--color-text-primary);">
                        <IconBarChart3 class="w-4 h-4" /> Responses
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('duplicateForm', item)" class="flex items-center gap-2 text-sm py-2" style="color: var(--color-text-primary);">
                        <IconFileText class="w-4 h-4" /> Duplicate
                      </a>
                    </li>
                    <li v-if="!item.is_active">
                      <a @click="$emit('activateForm', item)" class="flex items-center gap-2 text-sm py-2" style="color: var(--color-accent);">
                        <IconCheck class="w-4 h-4" /> Activate
                      </a>
                    </li>
                    <li>
                      <a @click="$emit('deleteForm', item)" class="flex items-center gap-2 text-sm py-2" style="color: var(--color-danger);">
                        <IconTrash2 class="w-4 h-4" /> Delete
                      </a>
                    </li>
                  </ul>
                </teleport>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="forms.length === 0" class="text-center py-16">
        <IconFileText class="w-16 h-16 mx-auto mb-4" style="color: var(--color-text-light);" />
        <div class="text-xl font-medium mb-2" style="color: var(--color-text-primary);">
          No Employment Tracer Forms Yet
        </div>
        <div class="text-sm mb-6 max-w-md mx-auto" style="color: var(--color-text-secondary);">
          Create your first employment tracer form to start collecting
          alumni employment data.
        </div>
        <button
          class="btn btn-primary-custom flex items-center gap-2 mx-auto"
          @click="$emit('createForm')"
        >
          <IconPlus class="w-4 h-4" />
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
    required: false,
    default: () => ({})
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

<style scoped>
/* Custom table styling */
.table th {
  background: var(--color-neutral);
  border-bottom: 2px solid var(--color-border);
  padding: 1rem 0.75rem;
  font-weight: 600;
}

.table td {
  border-bottom: 1px solid var(--color-border-light);
  padding: 1rem 0.75rem;
}

.table tr:hover {
  background: rgba(var(--color-primary-rgb), 0.05);
}

.table tr:last-child td {
  border-bottom: none;
}

/* Enhance dropdown menu styling */
.menu li > a:hover {
  background: rgba(var(--color-primary-rgb), 0.1);
  color: var(--color-primary);
}

/* Badge styling improvements */
.badge {
  font-weight: 500;
  font-size: 0.75rem;
}

/* Button hover effects */
.btn-circle:hover {
  background: var(--color-primary);
  transform: scale(1.05);
  transition: transform 0.2s ease;
}

/* Card shadow enhancement */
.card {
  border: 1px solid var(--color-border-light);
}
</style>
