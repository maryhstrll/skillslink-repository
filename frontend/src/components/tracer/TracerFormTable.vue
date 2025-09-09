<template>
  <DataTable
    title="Employment Tracer Forms"
    :title-icon="IconFileText"
    :count-icon="IconBarChart3"
    :data="forms"
    :columns="tableColumns"
    :loading="false"
    item-label="forms"
    empty-title="No Employment Tracer Forms Yet"
    empty-description="Create your first employment tracer form to start collecting alumni employment data."
    :empty-icon="IconFileText"
    key-field="form_id"
  >
    <!-- Custom cell for year with active badge -->
    <template #cell-form_year="{ item }">
      <div class="flex items-center gap-2">
        <span class="font-semibold" style="color: var(--color-text-primary);">{{ item.form_year }}</span>
        <span
          v-if="item.is_active"
          class="badge badge-sm"
          style="background: var(--color-accent); color: white; border: none;"
        >Active</span>
      </div>
    </template>

    <!-- Custom cell for form title with description -->
    <template #cell-form_title="{ item }">
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
    </template>

    <!-- Custom cell for status badge -->
    <template #cell-status="{ item }">
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
    </template>

    <!-- Custom cell for questions count -->
    <template #cell-questions="{ item }">
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
    </template>

    <!-- Custom cell for actions dropdown -->
    <template #cell-actions="{ item }">
      <div class="relative overflow-visible">
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
      </div>
    </template>

    <!-- Custom empty state with create button -->
    <template #empty-actions>
      <button
        class="btn btn-primary-custom flex items-center gap-2 mx-auto"
        @click="$emit('createForm')"
      >
        <IconPlus class="w-4 h-4" />
        Create Your First Employment Tracer
      </button>
    </template>
  </DataTable>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import DataTable from '@/components/tables/DataTable.vue'
import { 
  FileText as IconFileText,
  BarChart3 as IconBarChart3,
  Check as IconCheck,
  Users as IconUsers,
  MoreVertical as IconEllipsisV,
  Eye as IconEye,
  Edit as IconEdit,
  Trash2 as IconTrash2,
  Plus as IconPlus
} from 'lucide-vue-next'

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

// Table columns configuration
const tableColumns = computed(() => [
  {
    key: 'form_year',
    title: 'Year',
    headerClass: 'w-20',
    cellClass: 'font-semibold'
  },
  {
    key: 'form_title',
    title: 'Form Title',
    headerClass: 'min-w-[200px]'
  },
  {
    key: 'status',
    title: 'Status',
    headerClass: 'w-24'
  },
  {
    key: 'questions',
    title: 'Questions',
    headerClass: 'w-32'
  },
  {
    key: 'actions',
    title: 'Actions',
    headerClass: 'w-20'
  }
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
